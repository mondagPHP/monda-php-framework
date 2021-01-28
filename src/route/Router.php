<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\route;

use Doctrine\Common\Annotations\AnnotationReader;
use framework\annotation\RequestMethod;
use framework\annotation\ValidRequire;
use framework\Container;
use framework\exception\HeroException;
use framework\exception\RequestMethodException;
use framework\exception\RouteNotFoundException;
use framework\exception\ValidateException;
use framework\request\RequestInterface;
use framework\validate\RequestValidator;
use framework\vo\RequestVoInterface;

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
     * @throws HeroException|\ReflectionException
     */
    public function dispatch(RequestInterface $request)
    {
        $this->parseURL($request);
        $controller = "app\\modules\\{$this->module}\\action\\" . str_replace('/', '\\', $this->action) . 'Action';

        $classExist = class_exists($controller);
        if (!$classExist) {
            throw new RouteNotFoundException('找不到路由!');
        }
        //设置request controller requestMethod 参数
        $request->setControllerClass($controller);
        $request->setRequestMethod($this->method);

        $middlewareConfig = Container::getContainer()->get('config')->get('middleware', []);
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
            //收集注解
            $annotationValid = [];
            if ($this->isSetAnnotationOn()) {
                $annotationValid = $this->collectAnnotation($request, $reflectionMethod);
            }
            foreach ($reflectionParams ?? [] as $reflectionParam) {
                $paramName = $reflectionParam->getName();
                if (isset($requestParams[$paramName])) {
                    $param = $requestParams[$paramName];
                    if (is_scalar($requestParams[$paramName])) {
                        $param = trim($param);
                        if ($param === '') {
                            throw new ValidateException($paramName . "不能为空!");
                        }
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
                    //是否开启注解
                    if ($this->isSetAnnotationOn() && isset($annotationValid[$paramName])) {
                        throw new ValidateException($annotationValid[$paramName]);
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
     * 收集注解
     * @param RequestInterface $request
     * @param \ReflectionMethod $method
     * @return array
     */
    private function collectAnnotation(RequestInterface $request, \ReflectionMethod $method): array
    {
        $annotations = [];
        $reader = new AnnotationReader();
        $requestMethodAnnotation = $reader->getMethodAnnotation($method, RequestMethod::class);
        if ($requestMethodAnnotation !== null && strtolower($request->getMethod()) !== strtolower($requestMethodAnnotation->method)) {
            throw new RequestMethodException("请求的方法不一致，请检查!");
        }
        $readers = $reader->getMethodAnnotations($method);
        foreach ($readers ?? [] as $reader) {
            if (!$reader instanceof ValidRequire) {
                continue;
            }
            $annotations[$reader->name] = $reader->msg;
        }
        return $annotations;
    }


    /**
     * 注解是否打开
     * @return bool
     */
    private function isSetAnnotationOn(): bool
    {
        return defined("ANNOTATION") && ANNOTATION;
    }

    /**
     * 解析url.
     * @param RequestInterface $request
     */
    private function parseURL(RequestInterface $request): void
    {
        $defaultUrlArr = Container::getContainer()->get('config')->get('app.default_url');
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
        if (!$this->module) {
            $this->module = $defaultUrlArr['module'];
        }
        if (!$this->action) {
            $this->action = ucfirst($defaultUrlArr['action']);
        }
        if (!$this->method) {
            $this->method = $defaultUrlArr['method'];
        }
    }
}
