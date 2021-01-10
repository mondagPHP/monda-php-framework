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
}
