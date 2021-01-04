<?php

namespace framework\crypt;

/**
 * Class Ase
 * @package framework\crypt
 */
class Ase
{
    /**
     * 加密
     * @param string $str 要加密的数据
     * @param string $aseKey 要加密的数据
     * @return string   加密后的数据
     */
    public static function encrypt(string $str, string $aseKey): string
    {
        $data = openssl_encrypt($str, 'AES-128-ECB', $aseKey, OPENSSL_RAW_DATA);
        return base64_encode($data);
    }

    /**
     * 解密
     * @param string $str 要解密的数据
     * @param string $aseKey 要解密的数据
     * @return string        解密后的数据
     */
    public static function decrypt(string $str, string $aseKey): string
    {
        return openssl_decrypt(base64_decode($str), 'AES-128-ECB', $aseKey, OPENSSL_RAW_DATA);
    }
}
