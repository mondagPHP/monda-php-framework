<?php

namespace framework\cookie;

/**
 * Class Cookie
 * cookie 操作类
 */
class Cookie
{
    /*
     * @name 设置cookie
     * @param $name string 名称
     * @param $value mixed 值,字符串或数组
     * @param $options array 选项
     * @return 无
     */
    public static function set($name, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false): bool
    {
        $value = self::_encrypt(json_encode($value), 'E', config('app.app_key'));
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 删除cookie
     * @param $name
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     */
    public static function delete($name, $path = '', $domain = '', $secure = false, $httponly = false): void
    {
        setcookie($name, '', time() - 3600, $path, $domain, $secure, $httponly);
        unset($_COOKIE[$name]);
    }

    /**
     * 取出cookie
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public static function get($name, $default = null)
    {
        if (! isset($_COOKIE[$name])) {
            return $default;
        }
        $value = $_COOKIE[$name];
        $value = json_decode(self::_encrypt($value, 'D', config('app.app_key')), true);
        return $value;
    }

    /* 加密核心, 私有方法, 不可调用 */
    private static function _encrypt($string, $operation, $key)
    {
        $key = md5($key);
        $key_length = strlen($key);
        $string = $operation === 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
        $string_length = strlen($string);
        $randKey = $box = [];
        $result = '';
        for ($i = 0; $i <= 255; $i++) {
            $randKey[$i] = ord($key[$i % $key_length]);
            $box[$i] = $i;
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $randKey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation === 'D') {
            if (strpos($result, substr(md5(substr($result, 8) . $key), 0, 8)) === 0) {
                return substr($result, 8);
            }
            return '';
        }
        return str_replace('=', '', base64_encode($result));
    }
}
