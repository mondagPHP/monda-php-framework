<?php
namespace framework\annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use framework\request\RequestInterface;

class ActionCheck
{
    /** @var RequestInterface $request */
    public $request;
    private $handlers = [];
    private $isDefined;

    private function __construct()
    {
        $this->isDefined = $this->isSetAnnotationOn();
    }

    /**
     * @return ActionCheck
     * date 2021/2/1
     */
    public static function create(): self
    {
        static $instance;
        if ($instance) {
            return $instance;
        }
        return new self();
    }

    /**
     * @param RequestInterface $request
     * @param \ReflectionMethod $method
     * date 2021/2/1
     */
    public function check(RequestInterface $request, \ReflectionMethod $method): void
    {
        if (!$this->isDefined) {
            return;
        }
        $this->request = $request;
        $this->collect($method);
        foreach ($this->handlers as $handler) {
            $handler($this);
        }
    }

    /**
     * 注解是否打开
     * @return bool
     */
    private function isSetAnnotationOn(): bool
    {
        return defined('ANNOTATION') && ANNOTATION;
    }

    /**
     * @param \ReflectionMethod $method
     * date 2021/2/1
     */
    private function collect(\ReflectionMethod $method): void
    {
        $reader = new AnnotationReader();
        $annotations = $reader->getMethodAnnotations($method);
        /** @var Annotation $annotation */
        foreach ($annotations ?? [] as $annotation) {
            if (! method_exists($annotation, 'check')) {
                continue;
            }
            $this->handlers[] = $annotation->check();
        }
    }
}