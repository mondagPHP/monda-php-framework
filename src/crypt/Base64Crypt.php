<?php

namespace framework\crypt;

/*
 * Class Base64Crypt
 * @package framework\crypt
 */
class Base64Crypt
{
    /**
     * 加密字符串
     * @param $data
     * @param string $key 加密key
     * @param integer $expire 有效期（秒）
     * @return string
     */
    public static function encrypt(string $data, string $key, $expire = 0): string
    {
        $expire = sprintf('%010d', $expire ? $expire + time() : 0);
        $key = md5($key);
        $data = base64_encode($expire . $data);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }

            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
        }
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
    }

    /**
     * 解密字符串
     * @param $data
     * @param string $key 加密key
     * @return string
     */
    public static function decrypt($data, string $key): string
    {
        $key = md5($key);
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        $data = base64_decode($data);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }

            $char .= substr($key, $x, 1);
            $x++;
        }

        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        $data = base64_decode($str);
        $expire = substr($data, 0, 10);
        if ($expire > 0 && $expire < time()) {
            return '';
        }
        $data = substr($data, 10);
        return $data;
    }
}
