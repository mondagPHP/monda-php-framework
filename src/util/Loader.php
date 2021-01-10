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
     * @return mixed
     */
    public static function service($serviceClass)
    {
        if (! Container::getContainer()->has($serviceClass)) {
            Container::getContainer()->bind($serviceClass, $serviceClass, true);
        }
        return Container::getContainer()->get($serviceClass);
    }
}
