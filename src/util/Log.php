<?php


namespace framework\util;


use framework\Container;

/**
 * Class Log
 * @package framework\util
 * @method static void debug(string $str, array $context = [])
 * @method static void info(string $str, array $context = [])
 * @method static void notice(string $str, array $context = [])
 * @method static void warning(string $str, array $context = [])
 * @method static void error(string $str, array $context = [])
 * @method static void alert(string $str, array $context = [])
 * @method static void emergency(string $str, array $context = [])
 */
class Log
{

    /**
     * @param $name
     * @param $arguments
     */
    public static function __callStatic($name, $arguments): void
    {

        Container::getContainer()->get('log')->{$name}(...$arguments);
    }
}