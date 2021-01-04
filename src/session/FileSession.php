<?php

namespace framework\session;

use framework\file\FileUtils;
use framework\request\RequestInterface;

/**
 * Class FileSession
 * @package framework\session
 */
class FileSession extends \SessionHandler
{
    private $config;

    private $sessionSavePath;

    private $userIp;

    /**
     * FileSession constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->sessionSavePath = $config['session_save_path'];
        if (! file_exists($this->sessionSavePath)) {
            FileUtils::makeFileDirs($this->sessionSavePath);
        }
        if (! is_writable($this->sessionSavePath)) {
            throw new \RuntimeException('session 目录' . $this->sessionSavePath . '不可写，请更改权限。');
        }
        $this->userIp = app(RequestInterface::class)->getClientIp();
    }

    /**
     * 析构方法
     */
    public function __destruct()
    {
        session_write_close();
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function destroy($id): bool
    {
        $sessionFile = $this->sessionSavePath . DIRECTORY_SEPARATOR . $this->config['session_file_prefix'] . $id;
        if (file_exists($sessionFile)) {
            @unlink($sessionFile);
        }
        return true;
    }

    public function gc($max_lifetime): bool
    {
        $sessionFiles = glob($this->config['session_save_path'] . DIRECTORY_SEPARATOR . $this->config['session_file_prefix'] . '*');
        if (! empty($sessionFiles)) {
            foreach ($sessionFiles as $value) {
                if (filemtime($value) + $this->config['gc_maxlifetime'] < time()) {
                    @unlink($value);
                }
            }
        }
        return true;
    }

    public function read($id): string
    {
        $sessionFile = $this->sessionSavePath . DIRECTORY_SEPARATOR . $this->config['session_file_prefix'] . $id;
        if (file_exists($sessionFile)) {
            if (filemtime($sessionFile) + $this->config['gc_maxlifetime'] < time()) {
                $this->destroy($id);
                return '';
            }
            return file_get_contents($sessionFile);
        }
        //2. if user's ip address is changed, destroy session.
        if (app(RequestInterface::class)->getClientIp() != $this->userIp) {
            $this->destroy($id);
            return '';
        }
        return '';
    }

    public function write($id, $data): bool
    {
        $sessionFile = $this->sessionSavePath . DIRECTORY_SEPARATOR . $this->config['session_file_prefix'] . $id;
        //先获取session数据
        $sessionData = '';
        if (file_exists($sessionFile)) {
            $sessionData = file_get_contents($sessionFile);
        }
        //为减少服务器的负担，每30秒钟更新一次session或者session有改变时
        if ($sessionData != $data || (file_exists($sessionFile) && filemtime($sessionFile) + $this->config['session_update_interval']) < time()) {
            file_put_contents($sessionFile, $data);
        }
        return true;
    }
}
