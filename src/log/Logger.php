<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\log;


use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MLogger;

/**
 * Class Logger.
 */
class Logger implements LoggerInterface
{
    protected $loggers = [];

    /**
     * 是否允许日志写入
     * @var bool
     */
    protected $allowWrite = true;

    //默认配置
    protected $config = [
        'path' => RUNTIME_PATH . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR,
        'level' => 'debug',
        'channel' => 'stack',
        'max_files' => 0,
        'file_permission' => 0666,
        'close' => true,
    ];

    // 实例化并传入参数
    public function __construct()
    {
        if (!empty(config('log.default'))) {
            $this->config['channel'] = config('log.default');
        }
        $this->config = array_merge($this->config, config("log.channels." . config('log.default')));
        $this->allowWrite = $this->config['close'];
        if (substr($this->config['path'], -1) != DIRECTORY_SEPARATOR) {
            $this->config['path'] .= DIRECTORY_SEPARATOR;
        }
    }


    /**
     * 创建日志
     * @param $name
     * @return mixed
     */
    private function createLogger($name)
    {
        if (!isset($this->loggers[$name])) {
            $channel = $this->config['channel'];
            // 日志文件目录
            $path = $this->config['path'];
            // 日志保存时间
            $maxFiles = $this->config['max_files'];
            // 日志等级
            $level = MLogger::toMonologLevel($this->config['level']);
            // 权限
            $filePermission = $this->config['file_permission'];
            // 创建日志
            $logger = new MLogger($channel);
            // 日志文件相关操作
            if ($channel == "stack") {
                $handler = new StreamHandler("{$path}framework.log", $level, true, $filePermission);
                $formatter = new LineFormatter("%datetime% %level_name% %message% %context% %extra%\n", "Y-m-d H:i:s", false, true);
            } else {
                $handler = new RotatingFileHandler("{$path}{$name}.log", $maxFiles, $level, true, $filePermission);
                $formatter = new LineFormatter("%datetime% %message% %context% %extra%\n", "Y-m-d H:i:s", false, true);
            }
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            $this->loggers[$name] = $logger;
        }
        return $this->loggers[$name];
    }


    /**
     * 记录日志信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  string $level 日志级别
     * @param  array $context 替换内容
     * @return mixed
     */
    public function record($message, $level = 'info', array $context = [])
    {
        if (!$this->allowWrite) {
            return false;
        }
        $logger = $this->createLogger($level);
        $level = MLogger::toMonologLevel($level);
        if (!is_int($level)) $level = MLogger::INFO;
        $message = sprintf('%s', $message);
        return $logger->addRecord($level, $message, $context);
    }


    /**
     * 记录日志信息
     * @access public
     * @param  string $level 日志级别
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        if ($level == 'sql') {
            $level = 'debug';
        }
        $this->record($message, $level, $context);
    }

    /**
     * Sql
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function sql($message, array $context = array()): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function emergency($message, array $context = array()): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function alert($message, array $context = array()): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function critical($message, array $context = array()): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function error($message, array $context = array()): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function warning($message, array $context = array()): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function notice($message, array $context = array()): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function info($message, array $context = array()): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function debug($message, array $context = array()): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }
}
