<?php
/**
 * This file is part of Monda-PHP.
 */
namespace framework\string;

use framework\lock\SynLockFactory;

/**
 * Class StringUtils.
 */
class StringUtils
{
    public const UUID_LOCK_KEY = 'monda_php_uuid_lock_key';

    /**
     * 生成一个唯一分布式UUID,根据机器不同生成. 长度为18位。
     * 机器码(2位) + 时间(12位，精确到微秒).
     * @return mixed
     */
    public static function genGlobalUid()
    {
        $lock = SynLockFactory::getFileSynLock(self::UUID_LOCK_KEY);
        $lock->tryLock();
        usleep(2);
        //获取服务器时间，精确到毫秒
        $tArr = explode(' ', microtime());
        $tSec = $tArr[1];
        $mSec = $tArr[0];
        if (($sIdx = strpos($mSec, '.')) !== false) {
            $mSec = substr($mSec, $sIdx + 1);
        }
        //获取服务器节点信息
        if (! defined('SERVER_NODE')) {
            $node = 0x01;
        } else {
            $node = SERVER_NODE;
        }
        $lock->unlock();
        return sprintf('%02x%08x%08x', $node, $tSec, $mSec);
    }

    /**
     * 下划线转驼峰.
     * @param $str
     * @return string
     */
    public static function underline2hump($str): string
    {
        $str = trim($str);
        if (strpos($str, '_') === false) {
            return $str;
        }
        $arr = explode('_', $str);
        $str = $arr[0];
        for ($i = 1, $iMax = count($arr); $i < $iMax; ++$i) {
            $str .= ucfirst($arr[$i]);
        }
        return $str;
    }

    /**
     * 驼峰转下划线
     * @param $str
     * @return mixed
     */
    public static function hump2Underline($str): string
    {
        $arr = [];
        for ($i = 0, $iMax = strlen($str); $i < $iMax; ++$i) {
            if (ord($str[$i]) > 64 && ord($str[$i]) < 91) {
                $arr[] = '_' . strtolower($str[$i]);
            } else {
                $arr[] = $str[$i];
            }
        }
        return implode('', $arr);
    }

    /**
     * 将中文数组json编码
     * @param $array
     * @return string
     */
    public static function jsonEncode($array): string
    {
        return json_encode($array, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 中文 json 数据解码
     * @param $string
     * @return mixed
     */
    public static function jsonDecode($string)
    {
        $string = trim($string, "\xEF\xBB\xBF");
        return json_decode($string, true);
    }
}
