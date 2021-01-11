<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\util;

use framework\Container;

/**
 * Class Loader.
 */
class Loader
{
    /**
     * @param $serviceClass
     * @param bool $isSingleton
     * @return mixed
     */
    public static function service($serviceClass, $isSingleton = true)
    {
        if (!Container::getContainer()->has($serviceClass)) {
            Container::getContainer()->bind($serviceClass, $serviceClass, $isSingleton);
        }
        return Container::getContainer()->get($serviceClass);
    }


    /**
     * @param $serviceClass
     * @return mixed
     */
    public static function singleton($serviceClass)
    {
        return static::service($serviceClass, true);
    }
}
