<?php


namespace framework\annotation;


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
}