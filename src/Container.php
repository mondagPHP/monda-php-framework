<?php
/**
 * This file is part of Monda-PHP.
 *
 */

namespace framework;

use app\exception\HandleException;
use Closure;
use framework\cache\Cache;
use framework\config\Config;
use framework\log\Logger;
use framework\response\Response;
use framework\route\PipeLine;
use framework\route\Router;
use framework\view\frameworkView;
use framework\view\HerosphpView;
use framework\view\ViewInterface;
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
    {
        self::$instance = $this;
        //注册绑定
        $this->register();
        //服务注册，才能启动
        $this->boot();
    }

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
        if (! $concrete instanceof Closure) {
            // 如果具体实现不是闭包  那就生成闭包
            $concrete = static function ($app) use ($concrete) {
                /* @var Container $app */
                return $app->build($concrete);
            };
        }
        $this->binding[$id] = compact('concrete', 'isSingleton');
    }

    /**
     * @param $clazz
     * @return object
     * @throws ReflectionException
     */
    public function build($clazz): object
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
        $instance = $this->getDependencies($dependencies);
        // 跟new 类($instances); 一样了
        return $reflector->newInstanceArgs($instance);
    }

    /**
     * 返回当前App实例，单例.
     */
    public static function getContainer(): self
    {
        return self::$instance ?? self::$instance = new self();
    }

    //解析构造函数参数
    protected function getDependencies($parameters): array
    {
        $dependencies = []; // 当前类的所有依赖
        /** @var ReflectionParameter $parameter */
        foreach ($parameters ?? [] as $parameter) {
            if ($parameter->getClass()) {
                $dependencies[] = $this->get($parameter->getClass()->name);
            }
        }
        return $dependencies;
    }

    /**
     * 注册服务
     */
    protected function register(): void
    {
        $registers = [
            'response' => Response::class,
            'config' => Config::class,
            'log' => Logger::class,
            'router' => Router::class,
            'pipeline' => PipeLine::class,
            'exception' => HandleException::class,
            'cache' => Cache::class,
            ViewInterface::class => HerosphpView::class,
        ];
        foreach ($registers ?? [] as $name => $concrete) {
            $this->bind($name, $concrete, true);
        }
    }

    /**
     * boot.
     */
    protected function boot(): void
    {
        //初始化配置文件文件
        self::getContainer()->get('config')->init();
        self::getContainer()->get('exception')->init();
        self::getContainer()->get(ViewInterface::class)->init();
    }
}
