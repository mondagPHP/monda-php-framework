<?php
/** @noinspection ALL */

namespace framework\session;

use framework\db\Redis;
use Predis\Session\Handler;

/**
 * Class RedisSession
 * @package framework\session
 * redis session driver
 */
class RedisSession
{
    /**
     * RedisSession constructor.
     * @param array $config
     * 构造方法
     */
    public static function start(array $config = [])
    {
        if (isset($config['gc_maxlifetime'])) {
            $lifeTime = $config['gc_maxlifetime'];
        } else {
            $lifeTime = ini_get('session.gc_maxlifetime');
        }
        $client = Redis::getInstance();
        (new Handler($client, ['gc_maxlifetime' => $lifeTime]))->register();
        session_start();
    }
}
