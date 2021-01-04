<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\db;

use Predis\Client;

/**
 * Class RedisUtils.
 */
class Redis
{
    //redis实例
    private static $redis = null;

    private function __construct()
    {
    }

    /**
     * @return mixed
     */
    public static function getInstance(): ?Client
    {
        if (is_null(self::$redis)) {
            $config = config('cache.redis');
            $parameters = $config['parameters'] ?? [];
            $options = $config['options'] ?? [];
            $redis = new Client($parameters, $options);
            if ($redis === null) {
                throw new \RuntimeException('加载redis错误!');
            }
            self::$redis = $redis;
        }
        return self::$redis;
    }
}
