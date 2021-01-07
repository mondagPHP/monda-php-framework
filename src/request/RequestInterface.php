<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\request;

use framework\file\FileUpload;

/**
 * Interface RequestInterface.
 */
interface RequestInterface
{
    public function __construct($uri, $method, $headers); // 初始化

    public static function create($uri, $method, $headers); // 创建request对象

    public function getUri(); // 获取请求url

    public function getMethod(); // 获取请求方法

    public function getHeaders(); // 获取请求头

    public function getReferer(); //上一次请求地址

    public function getRequestParams(); //获取所有参数

    public function setParameter(string $name, $value); //设置参数

    public function getParameter(string $name, $default = null);

    public function getClientIp(); //获取IP地址

    //获取上传的文件
    public function getUploadFile(string $formKey): FileUpload;
}
