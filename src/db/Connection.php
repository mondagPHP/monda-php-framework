<?php

namespace framework\db;

use framework\log\Log;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;

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

            if (config('app.app_debug', true)) {
                $capsule->setEventDispatcher(new Dispatcher(new Container));
                //添加监听事件
                /** @var Dispatcher $dispatcher */
                $dispatcher = $capsule->getEventDispatcher();
                if (! $dispatcher->hasListeners(QueryExecuted::class)) {
                    $dispatcher->listen(QueryExecuted::class, function ($query) {
                        $sql = vsprintf(str_replace('?', "'%s'", $query->sql), $query->bindings) . " \t[" . $query->time . ' ms] ';
                        Log::debug($sql);
                    });
                }
            }
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
