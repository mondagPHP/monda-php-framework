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
                    session_set_save_handler(new RedisSession(config('session.redis')), true);
                    session_start();
                    break;
                default:
                    throw new HeroException('找不到驱动!');
            }
        }
    }

    /**
     * 获取session值
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : (is_callable($default) ? call_user_func($default) : $default);
    }

    /**
     * 设置session值
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
}
