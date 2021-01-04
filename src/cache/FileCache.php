<?php
/**
 * This file is part of Monda-PHP.
 *
 */
namespace framework\cache;

use framework\exception\HeroException;
use framework\file\FileUtils;

/**
 * Class FileCache.
 */
class FileCache implements ICache
{
    /**
     * 缓存目录.
     * @var
     */
    private $dir;

    /**
     * 权限.
     * @var
     */
    private $mode;

    /**
     * FileCache constructor.
     * @param $dir
     * @param $per
     */
    public function __construct($dir, $per)
    {
        $this->dir = $dir;
        $this->mode = $per;
        if (! $this->mkdir($this->dir, $this->mode)) {
            trigger_error("can't create cache director {$this->dir}", E_USER_WARNING);
        }
    }

    /**
     * Delete cache file by key.
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $filename = $this->getFilename($key);
        if (file_exists($filename) && ! unlink($filename)) {
            trigger_error("can't remove cache file {$filename}", E_USER_WARNING);
            return false;
        }

        return true;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $result = null;
        $filename = $this->getFilename($key);
        try {
            if (! file_exists($filename)) {
                throw new \RuntimeException("file not found {$filename}");
            }
            $meta = $this->getMeta($filename);
            if ($meta && (int) $meta['ex'] !== 0 && ($meta['ex'] < microtime(true))) {
                unlink($filename);
                throw new HeroException("file expire {$filename}");
            }
            $h = fopen($filename, 'rb');
            if ($h === false) {
                throw new HeroException("file not readable {$filename}");
            }
            $result = '';
            fgets($h); // read meta from first line
            while (! feof($h)) {
                $result .= fgets($h);
            }
            fclose($h);
            if ($meta && $meta['sr']) {
                $result = unserialize($result);
            }
        } catch (\Exception $e) {
        }
        return ! is_null($result) ? $result : (is_callable($default) ? call_user_func($default) : $default);
    }

    /**
     * @param string $key
     * @param mixed $content
     * @param int $expire
     * @return bool
     */
    public function set(string $key, $content, $expire = 0): bool
    {
        $filename = $this->getFilename($key);
        $expire = $expire ? (time() + $expire) : 0;
        if (! file_exists($filename)) {
            $dir = dirname($filename);
            if (! $this->mkdir($dir, $this->mode)) {
                trigger_error("can't create cache director: {$dir}", E_USER_WARNING);
                return false;
            }
        }
        $isSerialize = ! is_string($content);
        if ($isSerialize) {
            $content = serialize($content);
        }
        $meta = json_encode(['ex' => $expire, 'cr' => time(), 'sr' => $isSerialize], 1);
        $h = fopen($filename, 'wb');
        if (! $h) {
            trigger_error("can't create cache file: {$filename}", E_USER_WARNING);
            return false;
        }
        fwrite($h, $meta . PHP_EOL . $content);
        fclose($h);
        chmod($filename, $this->mode);

        return true;
    }

    /**
     * @param string $key
     * @param int $expire
     * @param \Closure $callback
     * @return mixed
     */
    public function remember(string $key, int $expire, \Closure $callback)
    {
        $value = $this->get($key, null);
        if (empty($value)) {
            $value = $callback();
            $this->set($key, $value, $expire);
        }
        return $value;
    }

    /**
     * Delete old cache and empty cache suborder
     * @param string $folder
     * @return bool
     */
    public function clean(string $folder = ''): bool
    {
        $folder = $folder ? $folder : $this->dir;
        $dirs = scandir($folder, 1);
        $files = 0;
        if ($dirs) {
            $files = count($dirs) - 2;
            foreach ($dirs as $name) {
                if (in_array($name, ['.', '..'])) {
                    continue;
                }
                if (is_dir($folder . '/' . $name)) {
                    if ($this->clean($folder . '/' . $name)) {
                        --$files;
                    }
                    continue;
                }
                $filename = $folder . '/' . $name;
                $meta = $this->getMeta($filename);
                if ($meta && ($meta['ex'] != 0)
                    && ($meta['ex'] < microtime(true))) {
                    if (file_exists($filename) && ! unlink($filename)) {
                        trigger_error("can't delete cache file {$filename}", E_USER_WARNING);
                    }
                }
            }
            if (! $files && ($this->dir != $folder)) {
                rmdir($folder);
            }
        }

        return ! $files;
    }

    /**
     * Get filename by key.
     * @param string $key
     * @return string
     */
    private function getFilename(string $key): string
    {
        return str_replace('//', '/', $this->dir . '/' . str_replace('_', '/', $key)) . '.cache';
    }

    /**
     * Get meta from file.
     * @param string $filename
     * @return array
     */
    private function getMeta(string $filename): array
    {
        if (! file_exists($filename)) {
            return [];
        }
        $fh = fopen($filename, 'rb');
        if (! $fh) {
            trigger_error("can't open file {$filename}", E_USER_WARNING);
            return [];
        }
        $line = fgets($fh);
        $result = $line ? json_decode($line, true) : '';
        fclose($fh);
        return $result;
    }

    /**
     * Create dir with change permission.
     * @param string $dir
     * @param int $perm
     * @return bool
     */
    private function mkdir(string $dir, int $perm): bool
    {
        if (! is_dir($dir) && FileUtils::makeFileDirs($dir)) {
            chmod($dir, $perm);
        }
        return is_dir($dir);
    }
}
