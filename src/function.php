<?php
/**
 * This file is part of Monda-PHP.
 *
 */

use framework\Container;
use framework\view\ViewInterface;

if (! function_exists('response')) {
    function response()
    {
        return Container::getContainer()->get('response');
    }
}

if (! function_exists('app')) {
    function app($name = null)
    {
        if ($name) {
            return Container::getContainer()->get($name);
        }
        return Container::getContainer();
    }
}

if (! function_exists('endView')) {
    function endView()
    {
        $time = microtime(true) - START_TIME;
        $memory = memory_get_usage() - START_MEMORY;
        echo '<br/><br/><br/><br/><br/><hr/>';
        echo '运行时间: ' . round($time * 1000, 2) . 'ms<br/>';
        echo '消耗内存: ' . round($memory / 1024 / 1024, 2) . 'm';
    }
}

if (! function_exists('config')) {
    function config($key, $default = null)
    {
        return Container::getContainer()->get('config')->get($key, $default);
    }
}

if (! function_exists('cache')) {
    function cache()
    {
        return Container::getContainer()->get('cache');
    }
}

/**
 * 模板渲染
 */
if (! function_exists('view')) {
    function view(string $path, array $params = [])
    {
        return Container::getContainer()->get(ViewInterface::class)->render($path, $params);
    }
}

/**
 * 打印函数, 打印变量(数据)
 */
if (! function_exists('__print')) {
    function __print()
    {
        $_args = func_get_args();  //获取函数的参数
        if (count($_args) < 1) {
            trigger_error('必须为print()参数');
            return;
        }
        echo '<div style="width:100%;text-align:left"><pre>';
        //循环输出参数
        foreach ($_args as $_a) {
            if (is_array($_a)) {
                print_r($_a);
                echo '<br />';
            } elseif (is_string($_a)) {
                echo $_a . '<br />';
            } else {
                var_dump($_a);
                echo '<br />';
            }
        }
        echo '</pre></div>';
    }
}

/**
 * 打印一行
 * @param $msg
 */
if (! function_exists('printLine')) {
    function printLine($msg)
    {
        echo("{$msg} \n");
    }
}

/**
 * 终端高亮打印绿色
 * @param $message
 */
if (! function_exists('printOk')) {
    function printOk($message)
    {
        printf("\033[32m\033[1m{$message}\033[0m\n");
    }
}

/**
 * 终端高亮打印红色
 * @param $message
 */
if (! function_exists('printError')) {
    function printError($message)
    {
        printf("\033[31m\033[1m{$message}\033[0m\n");
    }
}

/**
 * 终端高亮打印黄色
 * @param $message
 */
if (! function_exists('printWarning')) {
    function printWarning($message)
    {
        printf("\033[33m\033[1m{$message}\033[0m\n");
    }
}
