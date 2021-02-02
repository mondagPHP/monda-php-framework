<?php


namespace framework\annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use framework\exception\RequestMethodException;
use framework\exception\ValidateException;
use framework\util\ModelTransformUtils;
use framework\validate\Validate;

class Annotation
{
    private $isDefined;

    private $annotations;

    public function __construct(\ReflectionMethod $method)
    {
        $this->isDefined = $this->isSetAnnotationOn();
        $this->collectAnnotation($method);
    }

    /**
     * 检查请求方法
     * @param string $method
     */
    public function chkRequestMethod(string $method): void
    {
        if (!$this->isDefined) {
            return;
        }
        if ($this->annotations['method'] !== null && strtolower($method) !== strtolower($this->annotations['method'])) {
            throw new RequestMethodException("请求的方法不一致，请检查!");
        }
    }

    /**
     * 参数过滤
     * @param array $inputParams
     * @throws ValidateException
     * @throws \ReflectionException
     */
    public function paramFilters(array $inputParams): void
    {
        if (!$this->isDefined) {
            return;
        }
        foreach ($inputParams as $k => $v) {
            $this->validRequireFilter($k, $v);
            $this->voValidFilter($k, $v);
        }
    }

    /**
     * 注解是否打开
     * @return bool
     */
    private function isSetAnnotationOn(): bool
    {
        return defined("ANNOTATION") && ANNOTATION;
    }

    /**
     * 收集注解
     * @param \ReflectionMethod $method
     * @return void
     */
    private function collectAnnotation(\ReflectionMethod $method): void
    {
        if (!$this->isDefined) {
            return;
        }
        $reader = new AnnotationReader();
        $readers = $reader->getMethodAnnotations($method);
        foreach ($readers ?? [] as $reader) {
            $this->setRequestMethodAnnotation($reader);
            $this->setValidRequireAnnotation($reader);
            $this->setVoValidAnnotation($reader);
        }
    }

    /**
     * 处理RequestMethod注解类
     * @param $reader
     */
    private function setRequestMethodAnnotation($reader): void
    {
        if (!$reader instanceof RequestMethod) {
            return;
        }
        $this->annotations['method'] = $reader->method;
    }

    /**
     * 处理ValidRequire注解类
     * @param $reader
     */
    private function setValidRequireAnnotation($reader): void
    {
        if (!$reader instanceof ValidRequire) {
            return;
        }
        $this->annotations['validRequire'][$reader->name] = $reader->msg;
    }

    /**
     * 处理VoValid注解类
     * @param $reader
     */
    private function setVoValidAnnotation($reader): void
    {
        if (!$reader instanceof VoValid) {
            return;
        }
        $this->annotations['voValid'][$reader->name] = [
            'validator' => $reader->validator,
            'scene' => $reader->scene,
        ];
    }

    /**
     * ValidRequire注解类参数过滤
     * @param string $key
     * @param $object
     * @throws ValidateException
     */
    private function validRequireFilter(string $key, $object): void
    {
        $annotations = $this->annotations['validRequire'] ?? [];
        if (!isset($annotations[$key])) {
            return;
        }
        if ($object === false) {
            throw new ValidateException($annotations[$key]);
        }
    }

    /**
     * VoValid注解类参数过滤
     * @param string $key
     * @param $object
     * @throws ValidateException
     * @throws \ReflectionException
     */
    private function voValidFilter(string $key, $object): void
    {
        $annotations = $this->annotations['voValid'] ?? [];
        if (!isset($annotations[$key])) {
            return;
        }
        $validClazz = $annotations[$key]['validator'];
        $scene = $annotations[$key]['scene'];
        if (!empty($validClazz) && !empty($scene)) {
            $data = ModelTransformUtils::model2Map($object);
            /** @var Validate $validator */
            $validateClass = new \ReflectionClass($validClazz);
            $validator = $validateClass->newInstance();
            if (! $validator->scene($scene)->check($data)) {
                throw new ValidateException($validator->getError());
            }
        }
    }
}