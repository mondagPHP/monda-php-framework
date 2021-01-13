<?php
namespace framework\tests\route;

use framework\request\RequestInterface;
use framework\route\PipeLine;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * Class RoutePipeLineTest
 * @package framework\tests\route
 * @date 2021/1/13
 */
class RoutePipeLineTest extends TestCase
{
    /** @var RequestInterface|m\LegacyMockInterface|m\MockInterface $request */
    private $request;

    /** @var PipeLine $pipeLine */
    private $pipeLine;

    public function setUp(): void
    {
        $this->request = m::mock(RequestInterface::class);
        $this->pipeLine = new PipeLine();
    }

    public function tearDown(): void
    {
        $this->pipeLine = null;
    }

    /**
     * 测试中间件传递自定义参数
     * @date 2021/1/13
     */
    public function testMiddleWareCusArgs(): void
    {
        $closure = function (RequestInterface $request, $age) {
            return $age;
        };

        $this->pipeLine->setClasses([
            SendAge::class,
            GetAge::class
        ]);
        $age = $this->pipeLine->run($closure)($this->request);
        $this->assertEquals('12', $age);
    }

    /**
     * 测试正常中间件中间件传递
     * @date 2021/1/13
     */
    public function testNormalMiddleware(): void
    {
        $closure = function (RequestInterface $request) {
            return $request;
        };

        $this->pipeLine->setClasses([
            M1::class,
            M2::class
        ]);
        $ret = $this->pipeLine->run($closure)($this->request);
        $this->expectOutputString('M1M2');
        $this->assertEquals($this->request, $ret);
    }

    /**
     * 测试混合中间件传递参数
     * @date 2021/1/13
     */
    public function testMixedMiddleware(): void
    {
        $closure = function (RequestInterface $request) {
            return $request;
        };

        $this->pipeLine->setClasses([
            M1::class,
            SendAge::class,
            GetAge::class,
            M2::class,
            SendAge::class,
            M2::class,
        ]);
        $ret = $this->pipeLine->run($closure)($this->request);
        $this->expectOutputString('M112M2M2');
        $this->assertEquals($this->request, $ret);
    }

    /**
     * 测试中途停止
     * @date 2021/1/13
     */
    public function testStopMiddleware(): void
    {
        $closure = function (RequestInterface $request) {
            return $request;
        };

        $this->pipeLine->setClasses([
            M1::class,
            Stop::class,
            M2::class,
        ]);
        $ret = $this->pipeLine->run($closure)($this->request);
        $this->assertEquals('stop', $ret);
    }
}

class SendAge
{
    public function handle(RequestInterface $request, \Closure $next)
    {
        $age = 12;
        return $next($request, $age);
    }
}

class GetAge
{
    public function handle(RequestInterface $request, \Closure $next, $age)
    {
        echo $age;
        return $next($request, $age);
    }
}

class M1
{
    public function handle(RequestInterface $request, \Closure $next)
    {
        echo 'M1';
        return $next($request);
    }
}

class M2
{
    public function handle(RequestInterface $request, \Closure $next)
    {
        echo 'M2';
        return $next($request);
    }
}

class Stop
{
    public function handle(RequestInterface $request, \Closure $next)
    {
        return 'stop';
    }
}
