<?php
namespace framework\annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;
use framework\exception\ValidateException;
use framework\validate\Validate;

/**
 * Class RequestValidate
 * @Annotation
 * @Target("METHOD")
 * @package framework\annotation
 * date 2021/2/1
 */
class RequestValidate
{
    /** @var string $validate */
    public $validate;

    public $scene;

    /**
     * date 2021/2/1
     * @return \Closure
     */
    public function check(): \Closure
    {
        return function (ActionCheck $actionCheck) {
            if (! class_exists($this->validate)) {
                throw new ValidateException($this->validate . ' 验证类找不到');
            }
            /** @var Validate $validator */
            $validator = new $this->validate();
            if (! $validator instanceof Validate) {
                throw new ValidateException($this->validate . ' 不是验证类，请检查代码');
            }
            if (! $validator->scene($this->scene)->check($actionCheck->request->getRequestParams())) {
                throw new ValidateException($validator->getError());
            }
        };
    }
}
