<?php
namespace framework\annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use framework\exception\ValidateException;

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

    /**
     * @return \Closure
     * date 2021/2/1
     */
    public function check(): \Closure
    {
        return function (ActionCheck $actionCheck) {
            $params = $actionCheck->request->getRequestParams();
            if (! isset($params[$this->name])) {
                throw new ValidateException($this->msg);
            }
        };
    }
}
