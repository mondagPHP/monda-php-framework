<?php
/**
 * This file is part of Monda-PHP.
 *
 */
namespace framework\file;

/**
 * Class FileUtils.
 */
class FileUtils
{
    /**
     * 创建多层文件目录.
     * @param string $path 需要创建路径
     * @return bool 成功时返回true，失败则返回false;
     */
    public static function makeFileDirs($path): bool
    {
        //必须考虑 "/" 和 "\" 两种目录分隔符
        $files = preg_split('/[\/|\\\]/s', $path);
        $dir = '';
        foreach ($files as $value) {
            $dir .= $value . DIRECTORY_SEPARATOR;
            if (! file_exists($dir) && ! mkdir($dir, 0777) && ! is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }
        return true;
    }

    /**
     * 递归删除文件夹.
     * @param $dir
     * @return bool
     */
    public static function removeDirs($dir): bool
    {
        $handle = opendir($dir);
        //删除文件夹下面的文件
        while ($file = readdir($handle)) {
            if ($file !== '.' && $file !== '..') {
                $filename = $dir . '/' . $file;
                if (! is_dir($filename)) {
                    @unlink($filename);
                } else {
                    self::removeDirs($filename);
                }
            }
        }
        closedir($handle);
        //删除当前文件夹
        if (rmdir($dir)) {
            return true;
        }
        return false;
    }
    /**
     * 遍历目录，返回目录文件相对路径
     * @param $dir
     * @return array
     */
    public static function dirTraversal($dir): array
    {
        $files = array();
        self::getDirFiles($dir, '', $files);
        return $files;
    }

    /**
     * 获取目录文件
     * @param $absolute_dir //目录绝对路径
     * @param $relative_dir //目录相对路径
     * @param $files
     */
    private static function getDirFiles($absolute_dir, $relative_dir, &$files): void
    {
        $handler = opendir($absolute_dir);
        if ( $handler !== false ) {
            while ( $filename = readdir($handler) ) {
                if ( $filename !== "." && $filename !== ".." ) {
                    if ( is_dir($absolute_dir."/".$filename) ) {
                        self::getDirFiles($absolute_dir."/".$filename, $relative_dir.$filename."/", $files);
                    } else {
                        $files[] = $relative_dir.$filename;
                    }
                }
            }
            closedir($handler);
        }
    }

}
