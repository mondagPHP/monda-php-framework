<?php

namespace framework\annotation;

use framework\exception\ValidateException;

/**
 * 基本类型
 * Class valid
 * @Annotation
 * @Target({"METHOD"})
 */
class NotEmpty
{
    /** @var string */
    public $name;

    /** @var string */
    public $msg;

    public function __construct(array $param)
    {
        $this->name = $param['name'] ?? '';
        $this->msg = $param['msg'] ?? '';
    }

    /**
     * @return \Closure
     */
    public function check(): \Closure
    {
        return function (ActionCheck $actionCheck) {
            $params = $actionCheck->request->getRequestParams();
            $msg = $this->msg === '' ? $this->name . '不能为空!' : $this->msg;
            if (isset($params[$this->name]) && is_scalar($params[$this->name]) && trim($params[$this->name]) === '') {
                throw new ValidateException($msg);
            }
        };
    }
}
