<?php

namespace framework\validate;

use framework\exception\HeroException;
use framework\exception\ValidateException;
use framework\request\RequestInterface;
use framework\util\ModelTransformUtils;
use framework\vo\RequestVoInterface;
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
        if (! empty($vo->valid())) {
            $validClazz = $vo->valid()[0];
            $validScene = $vo->valid()[1];
            /** @var Validate $validator */
            $validateClass = new \ReflectionClass($validClazz);
            $validator = $validateClass->newInstance();
            if (! $validator->scene($validScene)->check($data)) {
                throw new ValidateException($validator->getError());
            }
        }
        return $vo;
    }
}
