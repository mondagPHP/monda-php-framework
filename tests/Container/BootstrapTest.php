<?php

namespace framework\tests\Container;

use framework\Bootstrap;
use framework\Container;
use framework\tests\Container\mock\MockConfig;
use framework\tests\Container\mock\MockHandleException;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testInstanceSingleton(): void
    {
        $first = Bootstrap::getInstance();
        $second = Bootstrap::getInstance();
        $this->assertSame($first, $second);
    }

    public function testSetRegisters(): void
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->setRegisters([
            'config' => MockConfig::class,
            'exception' => MockHandleException::class,
        ]);
        $registers = $bootstrap->getRegisters();
        $this->assertEquals([
            'config' => MockConfig::class,
            'exception' => MockHandleException::class,
        ], $registers);
    }

    /**
     * @depends testSetRegisters
     * @throws \ReflectionException
     */
    public function testInit(): void
    {
        Bootstrap::getInstance()->init();
        /** @var MockConfig $config */
        $config = Container::getContainer()->get('config');
        $name = $config->get('test.name');
        $this->assertEquals('bootstrapTest', $name);
    }
}
