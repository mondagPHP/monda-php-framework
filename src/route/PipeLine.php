<?php
/**
 * This file is part of Monda-PHP.
 *
 */
namespace framework\route;

use Closure;

/**
 * Class PipeLine
 * @package framework\route
 */
class PipeLine
{
    //所有要执行的类
    protected $classes = [];

    //类的方法名称
    protected $handleMethod = 'handle';

    //需要创建新对象
    public function create(): self
    {
        return clone $this;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setHandleMethod($method): self
    {
        $this->handleMethod = $method;
        return $this;
    }

    /**
     * @param $classes
     * @return $this
     */
    public function setClasses($classes): self
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * 管道操作.
     * @param Closure $initial
     * @return Closure
     */
    public function run(Closure $initial): Closure
    {
        return array_reduce(array_reverse($this->classes), function ($res, $currClass) {
            return function ($request, ...$cusArgs) use ($res, $currClass) {
                return (new $currClass())->{$this->handleMethod}($request, $res, ...$cusArgs);
            };
        }, $initial);
    }
}
