<?php


namespace framework\annotation;


/**
 * vo对象
 * Class valid
 * @Annotation
 * @Target({"METHOD"})
 */
class VoValid
{
    public $name;

    public $validator;

    public $scene;

    public function __construct(array $param)
    {
        $this->name = $param['name'] ?? '';
        $this->validator = $param['validator'] ?? '';
        $this->scene = $param['scene'] ?? '';
    }
}