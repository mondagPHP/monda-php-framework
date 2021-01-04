<?php

namespace framework\validate;

use framework\exception\HeroException;
use framework\exception\ValidateException;
use framework\request\RequestInterface;
use framework\util\ModelTransformUtils;
use ReflectionException;

/**
 * Class RequestValidator
 * @package framework\validate
 */
class RequestValidator
{
    /**
     * @param RequestInterface $request
     * @param string $classVo
     * @return object
     * @throws HeroException
     * @throws ReflectionException
     */
    public function valid(RequestInterface $request, string $classVo): object
    {
        $data = $request->getRequestParams();

        /** @var RequestVoInterface $vo */
        $vo = ModelTransformUtils::map2Model($classVo, $data);
        //检查Vo是否存在验证器
        //检查验证器是否存在
        if ($vo->getRequestValidator() !== '' && class_exists($vo->getRequestValidator())) {
            /** @var Validate $validator */
            $validateClass = new \ReflectionClass($vo->getRequestValidator());
            $validator = $validateClass->newInstance();
            if (! $validator->scene($vo->getRequestScene())->check($data)) {
                throw new ValidateException($validator->getError());
            }
        }
        return $vo;
    }
}
