<?php
/**
 * This file is part of Monda-PHP.
 *
 */
namespace framework\request;

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
        return isset($this->parameters[$name]) ?? is_callable($default) ? $default() : $default;
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
}
