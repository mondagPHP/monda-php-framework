<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\lock;

use framework\file\FileUtils;

/**
 * Class FileSynLock.
 */
class FileSynLock implements ISynLock
{
    private $fileHandler;  //文件资源柄

    public function __construct($key)
    {
        $lockDir = RUNTIME_PATH . '/lock/';
        FileUtils::makeFileDirs($lockDir);
        $this->fileHandler = fopen($lockDir . md5($key) . '.lock', 'wb');
    }

    /**
     * 去除.
     */
    public function __destruct()
    {
        if ($this->fileHandler !== false) {
            fclose($this->fileHandler);
        }
    }

    /**
     * 尝试去获取锁，成功返回false并且一直阻塞.
     */
    public function tryLock(): bool
    {
        return ! (flock($this->fileHandler, LOCK_EX) === false);
    }

    /**
     * 释放锁
     */
    public function unlock(): bool
    {
        return ! (flock($this->fileHandler, LOCK_UN) === false);
    }
}
