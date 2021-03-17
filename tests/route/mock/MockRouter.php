<?php
namespace framework\tests\route\mock;

use framework\annotation\ActionCheck;
use framework\exception\HeroException;
use framework\exception\RouteNotFoundException;
use framework\exception\ValidateException;
use framework\request\RequestInterface;
use framework\route\PipeLine;
use framework\route\Router;
use framework\validate\RequestValidator;
use framework\vo\RequestVoInterface;

class MockRouter extends Router
{
    /**
     * @param RequestInterface $request
     * @return mixed
     *               更具request执行路由
     * @throws RouteNotFoundException
     * @throws HeroException|\ReflectionException
     */
    public function dispatch(RequestInterface $request)
    {
        $this->parseURL($request);
        $controller = "framework\\tests\\route\\mock\\{$this->module}\\action\\" . str_replace('/', '\\', $this->action) . 'Action';

        $classExist = class_exists($controller);
        if (! $classExist) {
            throw new RouteNotFoundException('找不到路由!');
        }
        //设置request controller requestMethod 参数
        $request->setControllerClass($controller);
        $request->setRequestMethod($this->method);

        $middlewareConfig = [];
        $globalMiddleware = [];
        if (isset($middlewareConfig['global'])) {
            $globalMiddleware = array_merge($globalMiddleware, $middlewareConfig['global']);
        }
        if (isset($middlewareConfig[strtolower($this->module)])) {
            $globalMiddleware = array_merge($globalMiddleware, $middlewareConfig[strtolower($this->module)]);
        }
        $middleware = array_merge($globalMiddleware, call_user_func([$controller, 'getMiddleware'])); // 合并控制器中间件
        $method = $this->method;
        //分配路由
        $routerDispatch = function (RequestInterface $request) use ($controller, $method) {
            $requestParams = $request->getRequestParams();
            $inputParams = [];
            $controllerInstance = new $controller;
            $reflectionClass = new \ReflectionClass($controllerInstance);
            $reflectionMethod = $reflectionClass->getMethod($method);
            $reflectionParams = $reflectionMethod->getParameters();
            ActionCheck::create()->check($request, $reflectionMethod);

            foreach ($reflectionParams ?? [] as $reflectionParam) {
                $paramName = $reflectionParam->getName();
                if (isset($requestParams[$paramName])) {
                    $param = $requestParams[$paramName];
                    if (is_scalar($requestParams[$paramName])) {
                        $param = trim($param);
//                        if ($param === '') {
//                            throw new ValidateException($paramName . "不能为空!");
//                        }
                    }
                    $inputParams[] = $param;
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
        return (new PipeLine())->create()->setClasses($middleware)->run($routerDispatch)($request);
    }

    /**
     * 解析url.
     * @param RequestInterface $request
     */
    private function parseURL(RequestInterface $request): void
    {
        $defaultUrlArr = [
            'module' => 'index',
            'action' => 'index',
            'method' => 'index',

        ];
        //优先处理短链接映射
        $requestUri = $request->getUri();
        $urlInfo = parse_url($requestUri);
        if ($urlInfo['path'] && $urlInfo['path'] !== '/') {
            $pathInfo = explode('/', $urlInfo['path']);
            array_shift($pathInfo);
            $pathCount = count($pathInfo);

            if (isset($pathInfo[$pathCount - 2])) {
                $pathInfo[$pathCount - 2] = ucfirst($pathInfo[$pathCount - 2]);
            }

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
            $this->action = ucfirst($defaultUrlArr['action']);
        }
        if (! $this->method) {
            $this->method = $defaultUrlArr['method'];
        }
    }
}
