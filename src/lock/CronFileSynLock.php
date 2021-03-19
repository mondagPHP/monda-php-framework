<?php
namespace framework\lock;

use framework\file\FileUtils;
use framework\lock\exception\CronFileSynLockException;

/**
 * Class CronFileSynLock
 * @package framework\lock
 * date 2021/3/19
 */
class CronFileSynLock implements ISynLock
{
    private $lockFile;

    private $fileHandler;

    public function __construct(string $lockFile)
    {
        $lockDir = RUNTIME_PATH . '/cronLock/';
        FileUtils::makeFileDirs($lockDir);
        $this->lockFile = $lockDir . md5($lockFile) . '.lock';
        $this->registerError();
    }

    public function __destruct()
    {
        $this->clear();
    }

    /**
     * @return string
     * date 2021/3/19
     */
    public function getLockFile(): string
    {
        return $this->lockFile;
    }

    /**
     * @param $timeout 运行超时时常s //0不限制
     * @return bool
     * date 2021/3/19
     */
    public function tryLock($timeout = 0): bool
    {
        if (file_exists($this->lockFile)) {
            [$pid] = explode('|', file_get_contents($this->lockFile));
            if (function_exists('posix_getsid') && posix_getsid($pid) !== false) {
                return false;
            } elseif (file_exists('/proc/' . $pid)) {
                return false;
            }
        }
        $this->getHandler();
        if (flock($this->fileHandler, LOCK_EX) === false) {
            return false;
        }
        file_put_contents($this->lockFile, getmypid() . '|' . time() . '|' . $timeout);
        return true;
    }

    public function unLock(): bool
    {
        $this->getHandler();
        if (flock($this->fileHandler, LOCK_UN) === false) {
            return false;
        }
        $this->clear();
        return true;
    }

    /**
     * date 2021/3/19
     */
    public function clear(): void
    {
        if ($this->fileHandler) {
            @fclose($this->fileHandler);
            $this->fileHandler = null;
        }
        if ($this->lockFile && file_exists($this->lockFile)) {
            [$pid] = explode('|', file_get_contents($this->lockFile));
            if (getmypid() !== (int)$pid) {
                return;
            }
            @unlink($this->lockFile);
        }
    }

    /**
     * @return mixed
     * date 2021/3/19
     */
    private function getHandler()
    {
        if ($this->fileHandler) {
            return $this->fileHandler;
        }
        $this->fileHandler = fopen($this->lockFile, 'wb');
        if ($this->fileHandler === false) {
            throw new CronFileSynLockException('open cron lockFile err !!');
        }
    }

    /**
     * date 2021/3/19
     */
    private function registerError(): void
    {
        register_shutdown_function(function ($lock) {
            /** @var $lock CronFileSynLock */
            $lock->clear();
        }, $this);
    }
}
