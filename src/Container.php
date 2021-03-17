<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework;

use Closure;
use framework\exception\DependencyLoopException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * MondaPHP容器
 * Class Container.
 */
class Container implements ContainerInterface
{
    //绑定关系
    protected $binding = [];

    //container存储地方
    protected $instances = [];

    //app 实例对象
    private static $instance;

    /**
     * App constructor.
     */
    private function __construct()
    {}

    /**
     * @param string $id
     * @return mixed
     *               在容器取出key
     */
    public function get($id)
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        //找到是否在注册树,绑定的数据
        if (! isset($this->binding[$id])) {
            throw new \RuntimeException("找不到{$id}类,请先bind在注册树");
        }
        //查看是否存在该类存在
        $instance = $this->binding[$id]['concrete']($this);
        // 设置为单例
        if ($this->binding[$id]['isSingleton']) {
            $this->instances[$id] = $instance;
        }
        return $instance;
    }

    /**
     * @param $id
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->binding[$id]);
    }

    /**
     * @param $id
     * @param $concrete
     * @param bool $isSingleton
     */
    public function bind($id, $concrete, $isSingleton = false): void
    {
        //依赖栈
        $stack[$id] = 1;
        $this->bindWithStack($id, $concrete, $isSingleton, $stack);
    }

    /**
     * 返回当前App实例，单例.
     */
    public static function getContainer(): self
    {
        return self::$instance ?? self::$instance = new self();
    }

    /**
     * @param $id
     * @param $concrete
     * @param $isSingleton
     * @param array $stack
     */
    protected function bindWithStack($id, $concrete, $isSingleton, $stack = []): void
    {
        if (! $concrete instanceof Closure) {
            // 如果具体实现不是闭包  那就生成闭包
            $concrete = function ($app) use ($stack, $concrete) {
                /* @var Container $app */
                return $app->build($concrete, $stack);
            };
        }
        $this->binding[$id] = compact('concrete', 'isSingleton');
    }

    /**
     * @param $clazz
     * @param $stack
     * @return object
     * @throws DependencyLoopException
     * @throws ReflectionException
     */
    protected function build($clazz, $stack): object
    {
        //反射对象
        $reflector = new ReflectionClass($clazz);
        //构造函数
        $constructor = $reflector->getConstructor();
        //没有参数直接返回，实例对象
        if (is_null($constructor)) {
            return $reflector->newInstance();
        }
        //构造函数的参数对象
        $dependencies = $constructor->getParameters();
        // 当前类的所有实例化的依赖
        $instance = $this->getDependencies($dependencies, $stack);
        // 跟new 类($instances); 一样了
        return $reflector->newInstanceArgs($instance);
    }

    /**
     * 解析构造函数参数
     * @param $parameters
     * @param $stack
     * @return array
     * @throws DependencyLoopException
     * @throws ReflectionException
     */
    protected function getDependencies($parameters, $stack): array
    {
        $dependencies = []; // 当前类的所有依赖
        /** @var ReflectionParameter $parameter */
        foreach ($parameters ?? [] as $parameter) {
            if ($parameterClass = $parameter->getClass()) {
                //判断构造参数栈中是否存在重复的类
                if (isset($stack[$parameterClass->name])) {
                    throw new DependencyLoopException('类 ' . key($stack) . ' 存在依赖循环，重复依赖的类是 ' . $parameterClass->name .' 请检查代码');
                }
                if (! $this->has($parameterClass->name)) {
                    $stack[$parameterClass->getName()] = 1;
                    $this->bindWithStack($parameterClass->name, $parameterClass->name, false, $stack);
                }
                $dependency = $this->get($parameterClass->name);
            }  elseif ($parameter->isDefaultValueAvailable()) {
                $dependency = $parameter->getDefaultValue();
            } elseif ($parameter->getType()) {
                $dependency = [
                        'string' => '',
                        'int' => 0,
                        'array' => [],
                        'bool' => false,
                        'float' => 0.0,
                        'iterable' => [],
                        'callable' => function() {}
                    ][$parameter->getType()->getName()] ?? null;
            } else {
                $dependency = null;
            }
            $dependencies[] = $dependency;
        }
        return $dependencies;
    }
}
