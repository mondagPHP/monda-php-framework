<?php

namespace framework\annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * 请求方法
 * Class valid
 * @Annotation
 * @Target("METHOD")
 */
class RequestMethod
{
    public $method = 'get';
}