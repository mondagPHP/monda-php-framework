<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework;

/**
 * Class Controller.
 */
abstract class Controller
{
    protected $middleware = [];

    // 获取中间件
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        return call_user_func_array([$this, $method], $parameters);
    }
}
