<?php
/**
 * This file is part of Monda-PHP.
 *
 */
namespace framework\cache;

use framework\Container;
use framework\db\Redis;
use framework\exception\HeroException;

class Cache
{
    protected $channels = []; // 所有的实例化的通道  就是多例而已

    /**
     * @param $method
     * @param $parameters
     * @throws HeroException
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->channel()->{$method}(...$parameters);
    }

    /**
     * @throws HeroException
     * @return mixed
     */
    public function channel()
    {
        $name = Container::getContainer()->get('config')->get('cache.default');
        if (! method_exists($this, 'create' . ucfirst($name))) {
            throw new HeroException('driver不存在!');
        }

        return $this->channels[$name] ?? ($this->channels['name'] = $this->{'create' . ucfirst($name)}());
    }

    /**
     * file.
     */
    public function createFile(): ICache
    {
        $dir = config('cache.file.cache_dir');
        $per = config('cache.file.cache_per');
        return new FileCache($dir, $per);
    }

    /**
     * redis.
     */
    public function createRedis(): ICache
    {
        $client = Redis::getInstance();
        return new RedisCache($client);
    }
}
