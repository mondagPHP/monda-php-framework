<?php

namespace framework;

/**
 * Class Artisan
 * @package framework
 */
class Artisan
{
    private static $SHORT_OPS = 'hv';

    private static $LONG_OPTS = [
        'run:' => '执行一个客户脚本，参数是任务名称',
        'cron:' => '执行一个定时任务脚本，参数是任务名称',
    ];

    /**
     * 执行任务
     */
    public static function run(): void
    {
        $opts = getopt(self::$SHORT_OPS, array_keys(self::$LONG_OPTS));

        if (empty($opts) || isset($opts['help']) || isset($opts['h'])) {
            self::printHelpInfo();
        }
        if (isset($opts['version']) || isset($opts['v'])) {
            printLine('Version : 1.0');
        }
        if (isset($opts['run'])) { //运行任务
            try {
                $className = ucfirst($opts['run']) . 'Task';
                $clazz = new \ReflectionClass("app\\client\\{$className}");
                $method = $clazz->getMethod('run');
                $method->invoke($clazz->newInstance());
            } catch (\ReflectionException $exception) {
                printError('找不到任务!');
            }
        }
        if (isset($opts['cron'])) { //运行任务
            try {
                $cronPath = $opts['cron'];
                $pos = strrpos($cronPath, '\\');
                if ($pos === false) {
                    $className = ucfirst($opts['cron']) . 'Task';
                } else {
                    $className = substr($cronPath, 0, $pos + 1) . ucfirst(substr($cronPath, $pos + 1)) . 'Task';
                }
                $clazz = new \ReflectionClass("app\\cron\\{$className}");
                $method = $clazz->getMethod('run');
                $method->invoke($clazz->newInstance());
            } catch (\ReflectionException $exception) {
                printError('找不到任务!');
            }
        }
    }

    /**
     * 打印帮助信息
     */
    protected static function printHelpInfo(): void
    {
        printOk('Welcome to use monda-php artisan.');
        printLine('Version : 1.0');
        printOk('Usage: ');
        printLine('  ./artisan xxx');
        printLine('');
        printOk('Options: ');
        foreach (self::$LONG_OPTS as $key => $value) {
            $key = rtrim($key, ':');
            printLine("  --{$key} {$value}");
        }
    }
}
