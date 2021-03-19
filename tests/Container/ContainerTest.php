<?php

namespace framework\tests\Container;

use framework\config\Config;
use framework\Container;
use framework\exception\DependencyLoopException;
use framework\request\FpmRequest;
use framework\request\RequestInterface;
use framework\tests\Container\mock\DependencyLoopA;
use framework\tests\Container\mock\MultipleInjectionA;
use framework\tests\Container\mock\MultipleInjectionB;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testInstanceSingleton(): void
    {
        $first = Container::getContainer();
        $second = Container::getContainer();
        $this->assertSame($first, $second);
    }

    public function testNotHas(): void
    {
        $this->assertFalse(Container::getContainer()->has(RequestInterface::class));
    }

    public function testBind(): void
    {
        Container::getContainer()->bind(RequestInterface::class, FpmRequest::class);
        $this->assertTrue(Container::getContainer()->has(RequestInterface::class));
    }

    /**
     * @depends testBind
     */
    public function testGet(): void
    {
        $request = Container::getContainer()->get(RequestInterface::class);
        $this->assertInstanceOf(RequestInterface::class, $request);
    }

    public function testGetNotBind(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectDeprecationMessage('找不到config类,请先bind在注册树');
        Container::getContainer()->get('config');
    }

    public function testGetSingleton(): void
    {
        Container::getContainer()->bind('config', Config::class, true);
        $config1 = Container::getContainer()->get('config');
        $config2 = Container::getContainer()->get('config');
        $this->assertSame($config1, $config2);
    }

    public function testMultipleInjection(): void
    {
        Container::getContainer()->bind(MultipleInjectionA::class, MultipleInjectionA::class);
        /** @var MultipleInjectionA $aTool */
        $injectionA = Container::getContainer()->get(MultipleInjectionA::class);
        $config = $injectionA->getConfig();
        $hello = $injectionA->injectionBHello();
        $this->assertEquals([], $config);
        $this->assertEquals('in injectionB, Hello, zhangsan', $hello);
    }

    public function testInjectionConstructHaveDefault(): void
    {
        Container::getContainer()->bind(MultipleInjectionB::class, MultipleInjectionB::class);
        /** @var MultipleInjectionB $bTool */
        $injectionB = Container::getContainer()->get(MultipleInjectionB::class);
        $hello = $injectionB->hello();
        $this->assertEquals('in injectionB, Hello, zhangsan', $hello);
    }

    public function testDependencyLoop(): void
    {
        Container::getContainer()->bind('loop', DependencyLoopA::class);
        $this->expectException(DependencyLoopException::class);
        $this->expectErrorMessage('类 loop 存在依赖循环，重复依赖的类是 framework\tests\Container\mock\DependencyLoopB 请检查代码');
        Container::getContainer()->get('loop');
    }
}
