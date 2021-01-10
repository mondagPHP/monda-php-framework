<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\route;

use framework\Container;
use framework\exception\HeroException;
use framework\exception\RouteNotFoundException;
use framework\request\RequestInterface;
use framework\validate\RequestValidator;
use framework\vo\RequestVoInterface;
use Illuminate\Pagination\Paginator;

/**
 * Class Router.
 */
class Router
{
    /**
     * 模块.
     */
    protected $module;

    /**
     * 控制器.
     */
    protected $action;

    /**
     * 方法.
     */
    protected $method;

    /**
     * @param RequestInterface $request
     * @return mixed
     *               更具request执行路由
     * @throws RouteNotFoundException
     * @throws HeroException
     */
    public function dispatch(RequestInterface $request)
    {
        $this->parseURL($request);
        $controller = "app\\modules\\{$this->module}\\action\\" . ucfirst(str_replace('/', '\\', $this->action)) . 'Action';
        $classExist = class_exists($controller);
        if (! $classExist) {
            throw new RouteNotFoundException('找不到路由!');
        }
        $middlewareConfig = Container::getContainer()->get('config')->get('middleware', []);
        $globalMiddleware = [];
        if (isset($middlewareConfig['global'])) {
            $globalMiddleware = array_merge($globalMiddleware, $middlewareConfig['global']);
        }
        if (isset($middlewareConfig[strtolower($this->module)])) {
            $globalMiddleware = array_merge($globalMiddleware, $middlewareConfig[strtolower($this->module)]);
        }
        $controllerInstance = new $controller();
        $middleware = array_merge($globalMiddleware, $controllerInstance->getMiddleware()); // 合并控制器中间件
        $method = $this->method;

        //分配路由
        $routerDispatch = function (RequestInterface $request) use ($controllerInstance, $method) {
            $requestParams = $request->getRequestParams();
            $inputParams = [];
            //反射获取参数
            $reflectionClass = new \ReflectionClass($controllerInstance);
            $reflectionMethod = $reflectionClass->getMethod($method);
            $reflectionParams = $reflectionMethod->getParameters();
            foreach ($reflectionParams ?? [] as $reflectionParam) {
                if (isset($requestParams[$reflectionParam->getName()])) {
                    $inputParams[] = $requestParams[$reflectionParam->getName()];
                } else {
                    //对象
                    if (($reflectionParamClass = $reflectionParam->getClass()) !== null) {
                        //支持request
                        if ($reflectionParamClass->implementsInterface(RequestInterface::class)) {
                            $inputParams[] = $request;
                            continue;
                        }
                        //vo
                        if ($reflectionParamClass->implementsInterface(RequestVoInterface::class)) {
                            $inputParams[] = (new RequestValidator())->valid($request, $reflectionParamClass->getName());
                            continue;
                        }
                    }
                    $inputParams[] = false;
                }
            }
            //参数构造
            return $reflectionMethod->invokeArgs($controllerInstance, $inputParams);
        };
        return Container::getContainer()->get('pipeline')->create()->setClasses($middleware)->run($routerDispatch)($request);
    }

    /**
     * 解析url.
     * @param RequestInterface $request
     */
    private function parseURL(RequestInterface $request): void
    {
        //解析paginator
        $page = (int)$request->getParameter('page', 1);
        if ($page <= 0) {
            $page = 1;
        }
        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $defaultUrlArr = Container::getContainer()->get('config')->get('app.default_url');
        //优先处理短链接映射
        $requestUri = $request->getUri();
        $urlInfo = parse_url($requestUri);
        if ($urlInfo['path'] && $urlInfo['path'] !== '/') {
            $pathInfo = explode('/', $urlInfo['path']);
            array_shift($pathInfo);
            $pathCount = count($pathInfo);
            if (isset($pathInfo[0])) {
                $this->module = $pathInfo[0];
            }
            //pathInfo 小于等于3
            if ($pathCount <= 3) {
                if (isset($pathInfo[1])) {
                    $this->action = $pathInfo[1];
                }
                if (isset($pathInfo[2])) {
                    $this->method = $pathInfo[2];
                }
            } else {
                $action = '';
                for ($i = 1; $i < $pathCount; ++$i) {
                    if ($i === $pathCount - 1) {
                        continue;
                    }
                    $action .= $pathInfo[$i] . '/';
                }
                $action = substr($action, 0, -1);
                $this->action = $action;
                $this->method = $pathInfo[$pathCount - 1];
            }
        }
        //如果没有任何参数，则访问默认页面。如http://www.framework.my这种格式
        if (! $this->module) {
            $this->module = $defaultUrlArr['module'];
        }
        if (! $this->action) {
            $this->action = $defaultUrlArr['action'];
        }
        if (! $this->method) {
            $this->method = $defaultUrlArr['method'];
        }
    }
}
