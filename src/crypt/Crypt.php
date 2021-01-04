<?php

namespace framework\crypt;

class Crypt
{
    /**
     * 加密字符串
     * @param string $str 字符串
     * @param string $key 加密key
     * @param integer $expire 有效期（秒）
     * @return string
     */
    public static function encrypt(string $str, string $key, $expire = 0): string
    {
        $expire = sprintf('%010d', $expire ? $expire + time() : 0);
        $r = md5($key);
        $c = 0;
        $v = '';
        $str = $expire . $str;
        $len = strlen($str);
        $l = strlen($r);
        for ($i = 0; $i < $len; $i++) {
            if ($c == $l) {
                $c = 0;
            }

            $v .= substr($r, $c, 1) .
                (substr($str, $i, 1) ^ substr($r, $c, 1));
            $c++;
        }
        return self::ed($v, $key);
    }

    /**
     * 解密字符串
     * @param string $str 字符串
     * @param string $key 加密key
     * @return string
     */
    public static function decrypt(string $str, string $key): string
    {
        $str = self::ed($str, $key);
        $v = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $md5 = substr($str, $i, 1);
            $i++;
            $v .= (substr($str, $i, 1) ^ $md5);
        }
        $data = $v;
        $expire = substr($data, 0, 10);
        if ($expire > 0 && $expire < time()) {
            return '';
        }
        $data = substr($data, 10);
        return $data;
    }

    private static function ed($str, $key): string
    {
        $r = md5($key);
        $c = 0;
        $v = '';
        $len = strlen($str);
        $l = strlen($r);
        for ($i = 0; $i < $len; $i++) {
            if ($c == $l) {
                $c = 0;
            }

            $v .= substr($str, $i, 1) ^ substr($r, $c, 1);
            $c++;
        }
        return $v;
    }
}
