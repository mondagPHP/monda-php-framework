<?php
/**
 * This file is part of Monda-PHP.
 *
 */
namespace framework\log;

use framework\Container;
use framework\exception\HeroException;
use framework\log\driver\DailyLogger;
use framework\log\driver\StackLogger;

/**
 * Class Logger.
 */
class Logger
{
    protected $channels = []; // 所有的实例化的通道  就是多例而已

    protected $config;

    public function __construct()
    {
        $this->config = Container::getContainer()->get('config')->get('log');
    }

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
     * @param null $name
     * @throws HeroException
     * @return mixed
     */
    public function channel($name = null)
    {
        if (is_null($name)) {
            $name = $this->config['default'];
        }
        if (isset($this->channels[$name])) {
            return $this->channels[$name];
        }
        $config = Container::getContainer()->get('config')->get('log.channels.' . $name);

        if (! method_exists($this, 'create' . ucfirst($name))) {
            throw new HeroException('driver不存在!');
        }

        return $this->channels['name'] = $this->{'create' . ucfirst($name)}($config);
    }

    // 放在同一个文件
    public function createStack($config): StackLogger
    {
        return new StackLogger($config);
    }

    /**
     * 每日日志.
     * @param $config
     * @return DailyLogger
     */
    public function createDaily($config): DailyLogger
    {
        return new DailyLogger($config);
    }
}
