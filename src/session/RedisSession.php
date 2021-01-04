<?php
/** @noinspection ALL */

namespace framework\session;

use framework\db\Redis;
use Predis\Client;
use SessionHandler;

/**
 * Class RedisSession
 * @package framework\session
 * redis session driver
 */
class RedisSession extends SessionHandler
{
    /**
     * @var Client $handler
     */
    private $handler;

    private $lifeTime;

    private $prefix = 'H_SESSION:';

    /**
     * RedisSession constructor.
     * @param array $config
     * 构造方法
     */
    public function __construct(array $config = [])
    {
        if (isset($config['gc_maxlifetime'])) {
            $this->lifeTime = $config['gc_maxlifetime'];
        } else {
            $this->lifeTime = ini_get('session.gc_maxlifetime');
        }
        $this->handler = Redis::getInstance();
    }

    /**
     * 析构方法
     */
    public function __destruct()
    {
        session_write_close();
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function read($id)
    {
        return serialize($this->handler->get($this->prefix . $id));
    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        return (bool)$this->handler->setex($this->prefix . $id, $this->lifeTime, $data);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        return (bool)$this->handler->del($id);
    }

    /**
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function open($path, $name)
    {
        return true;
    }
}
