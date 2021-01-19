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
    private static $isFired = false;

    /**
     * set connect to db
     */
    public static function fireConnection(): void
    {
        //上一次的连接跟本次的一样，无需在加载连接
        if (!self::$isFired) {
            //加载配置
            $configs = config('database', []);
            $capsule = new Manager();
            foreach ($configs as $connection => $config) {
                $capsule->addConnection($config, $connection);
            }
            $capsule->bootEloquent();
            $capsule->setAsGlobal();
            if (config('app.app_debug', true)) {
                //添加监听事件
                $capsule->setEventDispatcher(new Dispatcher(new Container));
                /** @var Dispatcher $dispatcher */
                $dispatcher = $capsule->getEventDispatcher();
                if (!$dispatcher->hasListeners(QueryExecuted::class)) {
                    $dispatcher->listen(QueryExecuted::class, function ($query) {
                        $sql = vsprintf(str_replace('?', "'%s'", $query->sql), $query->bindings) . " \t[" . $query->time . ' ms] ';
                        Log::debug($sql);
                    });
                }
            }
            self::$isFired = true;
        }
    }
}
