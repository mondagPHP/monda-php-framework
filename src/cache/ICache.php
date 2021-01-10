<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\cache;

/**
 * Interface ICache.
 */
interface ICache
{
    /**
     * 获取缓存内容.
     *
     * @param mixed $key 缓存的key值,如果设置为null则自动生成key
     * @param null $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * 添加|更新缓存.
     * @param mixed $key 缓存的key值, 如果设置为null则自动生成key
     * @param mixed $content 缓存内容
     * @param int $expire 缓存有效期,如果等于0表示永不过期
     */
    public function set(string $key, $content, $expire = 0);

    /**
     * 删除缓存.
     * @param mixed $key
     * @return mixed
     */
    public function delete(string $key);

    /**
     * @param string $key
     * @param int $expire
     * @param \Closure $callback
     * @return mixed
     *               查找缓存，不存在就执行callback
     */
    public function remember(string $key, int $expire, \Closure $callback);
}
