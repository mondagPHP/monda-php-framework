<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\session;

use framework\exception\HeroException;

/**
 * Class Session
 * @package framework\session
 * session
 */
class Session
{
    /**
     * 开启session
     * @throws HeroException
     */
    public static function start(): void
    {
        if (! isset($_SESSION)) {
            switch (config('session.session_handler')) {
                case 'file':
                    session_set_save_handler(new FileSession(config('session.file')), true);
                    session_start();
                    break;
                case 'redis':
                    RedisSession::start(config('session.redis'));
                    break;
                default:
                    throw new HeroException('找不到驱动!');
            }
        }
    }

    /**
     * 重置session的所有的数据
     * @return void
     */
    public static function destroy(): void
    {
        session_destroy();
    }

    /**
     * 删除session某个key
     *
     * @param string $key
     * @return void
     */
    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * 获取session值
     * @param string $key
     * @param $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * 设置session值
     * @param $key
     * @param $value
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
}
