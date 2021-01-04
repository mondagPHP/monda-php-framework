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
     * @param bool $singleton
     * @return mixed
     */
    public static function service($serviceClass, $singleton = true)
    {
        if (! Container::getContainer()->has($serviceClass)) {
            Container::getContainer()->bind($serviceClass, $serviceClass, $singleton);
        }
        return Container::getContainer()->get($serviceClass);
    }
}
