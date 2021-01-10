<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\log\driver;

use framework\file\FileUtils;
use Psr\Log\AbstractLogger;

/**
 * 每天记录
 * Class DailyLogger.
 */
class DailyLogger extends AbstractLogger
{
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string $message 原本消息
     * @param array $context 要替换的
     * @return string
     */
    public function placeContext(string $message, array $context = []): string
    {
        $replace = [];
        foreach ($context ?? [] as $key => $val) {
            // 检查该值是否可以转换为字符串
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * @param mixed $level 级别
     * @param mixed $message 信息
     * @param array $context 上下文
     */
    public function log($level, $message, array $context = []): void
    {
        $destination = $this->config['path'] . date('Y_m_d') . '.log';
        $message = $this->placeContext($message, $context);
        $message = sprintf($this->config['format'], date('H:i:s'), $level, $message) . PHP_EOL;
        if (!is_dir($this->config['path'])) {
            FileUtils::makeFileDirs($this->config['path']);
        }
        error_log($message, 3, $destination);
    }
}
