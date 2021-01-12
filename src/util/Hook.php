<?php

namespace framework\util;

use framework\exception\HeroException;
use ReflectionMethod;

/**
 * Class Hook
 * @package app\utils\hook
 * 钩子
 */
class Hook
{
    protected static $hook = [];

    /**
     * 插件注册
     * @param [type] $clazz [类or对象]
     */
    public static function add($clazz): void
    {
        try {
            $refClass = new \ReflectionClass($clazz);
            $methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods ?? [] as $method) {
                if (isset(self::$hook[$method->getName()])) {
                    continue;
                }
                self::$hook[$method->getName()] = $clazz;
            }
        } catch (\ReflectionException $e) {
        }
    }

    /**
     * 插件执行
     * @param  [type] $name [description]
     * @param mixed ...$args
     * @return void [type]       [description]
     * @throws HeroException
     */
    public static function run($name, ...$args): void
    {
        if (isset(self::$hook[$name])) {
            $method = (new self::$hook[$name]());
            $method->$name(...$args);
        } else {
            throw new HeroException("该hook还没注册");
        }
    }
}