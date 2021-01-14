<?php
namespace framework\annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * 基本类型
 * Class valid
 * @Annotation
 * @Target({"METHOD"})
 */
class ValidRequire
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $msg;

    public function __construct(array $param)
    {
        $this->name = $param['name'] ?? '';
        $this->msg = $param['msg'] ?? '';
    }
}