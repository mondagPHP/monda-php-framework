<?php
/**
 * This file is part of Monda-PHP.
 *
 */
namespace framework\util;

use framework\exception\HeroException;
use framework\string\StringUtils;

/**
 * Class ModelTransformUtils.
 */
class ModelTransformUtils
{
    /**
     * map转换为数据模型.
     * @param string $class
     * @param array $map
     * @return object
     * @throws HeroException
     * @throws \ReflectionException
     */
    public static function map2Model(string $class, array $map = []): object
    {
        $refClass = new \ReflectionClass($class);
        $obj = $refClass->newInstance();
        $methodName = null;
        $method = null;
        foreach ($map as $key => $value) {
            $methodName = 'set' . ucwords(StringUtils::underline2hump($key));
            if ($refClass->hasMethod($methodName)) {
                $method = $refClass->getMethod($methodName);
                try {
                    $method->invoke($obj, $map[$key]);
                } catch (\Exception $e) {
                    throw new HeroException($e->getMessage());
                }
            }
        }
        return $obj;
    }

    /**
     * 模型对象转为map.
     * @param $model
     * @return array
     * @throws \ReflectionException
     */
    public static function model2Map($model): array
    {
        $refClass = new \ReflectionClass($model);
        $properties = $refClass->getProperties();
        $map = [];
        foreach ($properties as $value) {
            $property = $value->getName();
            if (strpos($property, '_')) {
                $property = StringUtils::underline2hump($property); //转换成驼锋格式
            }
            $method = 'get' . ucfirst($property);
            $map[$property] = $model->{$method}();
        }
        return $map;
    }
}
