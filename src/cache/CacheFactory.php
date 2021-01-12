<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\cache;

use framework\db\Redis;

/**
 * Class CacheFactory
 * @package framework\cache
 * 生成Cache
 */
class CacheFactory
{
    private static $instances = []; // 所有的实例化的通道  就是多例而已

    /**
     * @param string|null $type
     * @return ICache
     */
    public static function get(string $type = null): ICache
    {
        $config = config('cache');
        $type = $type === null ? $config['default'] : strtolower($type);
        if (!isset(self::$instances[$type])) {
            switch ($type) {
                case 'file':
                    self::$instances[$type] = new FileCache($config['file']['cache_dir']);
                    break;
                case 'redis':
                    self::$instances[$type] = new RedisCache(Redis::getInstance());
                    break;
                default:
                    throw new \RuntimeException('找不到cache的驱动');
            }
        }
        return self::$instances[$type];
    }


    /**
     * @param $classPath
     * @return mixed
     */
    public static function create($classPath)
    {
        $type = null;
        switch ($classPath) {
            case FileCache::class:
                $type = 'file';
                break;
            case RedisCache::class:
                $type = 'redis';
                break;
        }
        return self::get($type);
    }
}
