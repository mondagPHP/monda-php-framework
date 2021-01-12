<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\request;

use framework\exception\UploadException;
use framework\file\FileUpload;

/**
 * Class FpmRequest.
 */
class FpmRequest implements RequestInterface
{
    /**
     * url.
     */
    protected $uri;

    /**
     * 请求方法.
     */
    protected $method;

    /**
     * 所有请求头.
     */
    protected $headers;

    /**
     * 上次请求的url.
     * @var
     */
    protected $referer;

    protected $requestParams = [];

    public function __construct($uri, $method, $headers)
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->headers = $headers;
        $this->referer = $headers['HTTP_REFERER'] ?? '/';
        $requestParams = array_merge($_GET, $_POST);
        if (isset($headers['HTTP_CONTENT_TYPE']) && $headers['HTTP_CONTENT_TYPE'] === 'application/json') {
            $json = json_decode(file_get_contents('php://input'), 1);
            if (! is_null($json)) {
                $requestParams += $json;
            }
        }
        $this->requestParams = $requestParams;
    }

    // 创建一个请求
    public static function create($uri, $method, $headers = []): self
    {
        return new static($uri, $method, $headers); // new 自己
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return mixed
     */
    public function getReferer(): string
    {
        return $this->referer;
    }

    /**
     * @return array|mixed
     */
    public function getRequestParams()
    {
        return $this->requestParams;
    }

    public function getParameter(string $name, $default = null)
    {
        return isset($this->requestParams[$name]) ? $this->requestParams[$name] : $default;
    }

    /**
     * 设置参数.
     * @param string $name
     * @param $value
     */
    public function setParameter(string $name, $value): void
    {
        $this->requestParams[$name] = $value;
    }

    /**
     * 获取访问的IP地址
     */
    public function getClientIp(): string
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else {
            $ip = $this->headers['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * 获取文件上传
     * @param string $formKey
     * @return FileUpload
     * @throws UploadException
     */
    public function getUploadFile(string $formKey): FileUpload
    {
        $localFile = $_FILES[$formKey]['name'];
        $tempFile = $_FILES[$formKey]['tmp_name'];//原来是这样
        // 函数判断指定的文件是否是通过 HTTP POST 上传的。
        if (! is_uploaded_file($tempFile)) {
            throw new UploadException('上传的方式错误!');
        }
        return new FileUpload($localFile, $tempFile);
    }

    public function getFullUrl(): string
    {
        $requestUri = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
        } else {
            if (isset($_SERVER['argv'])) {
                $requestUri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['argv'][0];
            } elseif (isset($_SERVER['QUERY_STRING'])) {
                $requestUri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
            }
        }
        $scheme = empty($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
        $protocol = strstr(strtolower($_SERVER['SERVER_PROTOCOL']), '/', true) . $scheme;       //端口还是蛮重要的，毕竟需要兼容特殊的场景
        $port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (':' . $_SERVER['SERVER_PORT']);
        # 获取的完整url
        return $protocol . '://' . $_SERVER['SERVER_NAME'] . $port . $requestUri;
    }
}
