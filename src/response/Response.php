<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework\response;

/**
 * Class Response.
 */
class Response
{
    //默认是text/html
    protected $headers = [
        'Content-Type' => 'text/html;charset=utf-8',
    ]; // 要发送的请求头

    protected $content; // 要发送的内容

    protected $code = 200; // 发送状态码

    public function sendContent(): void // 发送内容
    {
        echo $this->content;
    }

    public function sendHeaders(): void // 发送请求头
    {
        foreach ($this->headers ?? [] as $key => $header) {
            header($key . ': ' . $header);
        }
    }

    public function send(): self // 发送
    {
        $this->sendHeaders();
        $this->sendContent();
        return $this;
    }

    public function setContent($content): self // 设置内容
    {
        if (is_array($content) || is_object($content)) {
            $this->headers['Content-type'] = 'application/json;charset=utf-8';
        }

        if (is_array($content)) {
            $content = json_encode($content);
        }
        $this->content = $content;
        return $this;
    }

    public function getContent(): string // 获取内容
    {
        return $this->content;
    }

    public function getStatusCode(): int    // 获取状态码
    {
        return $this->code;
    }

    public function setCode(int $code): self // 设置状态码
    {
        $this->code = $code;
        return $this;
    }
}
