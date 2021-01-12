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
    protected static $middleware = [];

    /**
     * 注册initialize方法
     * Controller constructor.
     */
    public function __construct()
    {
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }

    // 获取中间件
    public static function getMiddleware(): array
    {
        return static::$middleware;
    }
}
