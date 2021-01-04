<?php

namespace framework\db;

use Illuminate\Database\Capsule\Manager;

/**
 * Class Connection
 * @package core\db
 * 连接管理
 */
class Connection
{
    private static $fired = [];
    private static $currConnection = null;

    /**
     * @param $connection
     */
    public static function fireConnection($connection): void
    {
        //上一次的连接跟本次的一样，无需在加载连接
        if (self::$currConnection !== $connection) {
            //加载配置
            $config = config('database.' . $connection, []);
            //之前没有实例连接过
            if (! isset(self::$fired[$connection])) {
                $capsule = new Manager();
                $capsule->addConnection($config, $connection);
                self::setConnectionFired($connection, $capsule);
            } else {
                /** @var Manager $capsule */
                $capsule = self::$fired[$connection];
            }
            $capsule->bootEloquent();
            $capsule->setAsGlobal();
            self::$currConnection = $connection;
        }
    }

    /**
     * 设置已经初始化
     * @param string $connection
     * @param Manager $capsule
     */
    private static function setConnectionFired(string $connection, Manager $capsule): void
    {
        self::$fired[$connection] = $capsule;
    }
}
