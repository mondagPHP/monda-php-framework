<?php
/**
 * This file is part of Monda-PHP.
 *
 */
namespace framework\lock;

/**
 * Class SynLockFactory.
 */
class SynLockFactory
{
    private static $FILE_LOCK_POOL = []; //文件锁池

    /**
     * 获取文件锁
     * @param $key
     * @return ISynLock
     */
    public static function getFileSynLock($key): ISynLock
    {
        if (! isset(self::$FILE_LOCK_POOL[$key])) {
            self::$FILE_LOCK_POOL[$key] = new FileSynLock($key);
        }
        return self::$FILE_LOCK_POOL[$key];
    }
}
