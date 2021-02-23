<?php

namespace framework\annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use framework\exception\RequestMethodException;

/**
 * 请求方法
 * Class valid
 * @Annotation
 * @Target("METHOD")
 */
class RequestMethod
{
    /**
     * @Enum({"POST", "GET", "PUT", "DELETE", "post", "get", "put", "delete"})
     * @var string
     */
    public $method = 'GET';

    /**
     * @return \Closure
     * date 2021/2/1
     */
    public function check(): \Closure
    {
        return function (ActionCheck $actionCheck) {
            if (strtoupper($actionCheck->request->getMethod()) !== strtoupper($this->method)) {
                throw new RequestMethodException('请求方法不对，需要是:' . $this->method);
            }
        };
    }
}