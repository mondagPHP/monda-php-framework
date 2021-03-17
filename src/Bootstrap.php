<?php


namespace framework;


use app\exception\HandleException;
use framework\config\Config;
use ReflectionClass;

class Bootstrap
{
    /**
     * @var array
     */
    private $registers = [
        'config' => Config::class,
        'exception' => HandleException::class,
    ];

    private static $instance;

    /**
     * 获取实例
     * @return static
     */
    public static function getInstance(): self
    {
        return self::$instance ?? self::$instance = new self();
    }

    /**
     * 设置要注册的服务
     * @param array $registers
     * @return Bootstrap
     */
    public function setRegisters(array $registers): Bootstrap
    {
        $this->registers = array_merge($this->registers, $registers);
        return $this;
    }

    /**
     * @return array
     */
    public function getRegisters(): array
    {
        return $this->registers;
    }

    /**
     * 初始化
     * @return $this
     * @throws \ReflectionException
     */
    public function init(): Bootstrap
    {
        $this->register();
        $this->boot();
        return $this;
    }

    /**
     * 注册服务
     */
    private function register(): void
    {
        foreach ($this->registers ?? [] as $name => $concrete) {
            Container::getContainer()->bind($name, $concrete, true);
        }
    }

    /**
     * boot
     * @throws \ReflectionException
     */
    private function boot(): void
    {
        foreach ($this->registers ?? [] as $name => $concrete) {
            $reflector = new ReflectionClass($concrete);
            if ($reflector->hasMethod('init')) {
                Container::getContainer()->get($name)->init();
            }
        }
    }

    private function __construct()
    {}
}