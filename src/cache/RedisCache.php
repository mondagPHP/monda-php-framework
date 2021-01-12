<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\cache;

use Closure;
use Predis\Client;

/**
 * Class RedisCache.
 */
class RedisCache implements ICache
{
    /** @var Client */
    private $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $result = $this->redis->get($key);
        return ! is_null($result) ? $result : $default;
    }

    /**
     * @param string $key
     * @param mixed $content
     * @param int $expire
     * @return bool
     */
    public function set(string $key, $content, $expire = 0): bool
    {
        if ($expire > 0) {
            $r = $this->redis->set($key, $content, 'ex', $expire);
        } else {
            $r = $this->redis->set($key, $content);
        }
        return (bool)$r;
    }

    /**
     * 删除.
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return (bool)$this->redis->del($key);
    }

    /**
     * 删除.
     * @param string $key
     * @return bool
     */
    public function del(string $key): bool
    {
        return $this->delete($key);
    }

    /**
     * @param string $key
     * @param int $expire
     * @param Closure $callback
     * @return mixed
     */
    public function remember(string $key, int $expire, Closure $callback)
    {
        $value = $this->get($key, null);
        if (empty($value)) {
            $value = $callback();
            $this->set($key, $value, $expire);
        }
        return $value;
    }
}
