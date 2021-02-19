<?php

namespace framework\tests\route;

use framework\exception\RequestMethodException;
use framework\exception\ValidateException;
use framework\request\FpmRequest;
use framework\tests\route\mock\MockRouter;
use PHPUnit\Framework\TestCase;

class RouterAnnotationTest extends TestCase
{
    /** @var MockRouter $router */
    private $router;

    public function setUp(): void
    {

        defined('ANNOTATION') ?: define('ANNOTATION', true);
        $this->router = new MockRouter();
        $_GET = [];
    }

    public function testAnnotationValidRequire(): void
    {
        $request = new FpmRequest('/test/test/requireId', 'GET', []);
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('请传入id');
        $this->router->dispatch($request);
    }

    public function testAnnotationEmptyValidRequire(): void
    {
        $_GET['id'] = '    ';
        $request = new FpmRequest('/test/test/requireId', 'GET', []);
        $res = $this->router->dispatch($request);
        $this->expectOutputString('{"code":"000","success":true,"message":"操作成功","data":{"id":""}}');
        echo $res;
    }

    public function testAnnotationMoreValidRequire(): void
    {
        $_GET['id'] = '111';
        $_GET['name'] = '  ';
        $request = new FpmRequest('/test/test/requireMore', 'GET', []);
        $res = $this->router->dispatch($request);
        $this->expectOutputString('{"code":"000","success":true,"message":"操作成功","data":{"id":"111","name":""}}');
        echo $res;
    }

    public function testAnnotationNotEmpty(): void
    {
        $_GET['id'] = '    ';
        $request = new FpmRequest('/test/test/requireIdNotEmpty', 'GET', []);
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('id不能为空');
        $this->router->dispatch($request);
    }

    public function testAnnotationNotEmpty2(): void
    {
        $_GET['id'] = 'tom';
        $request = new FpmRequest('/test/test/requireIdNotEmpty', 'GET', []);
        $res = $this->router->dispatch($request);
        $this->expectOutputString('{"code":"000","success":true,"message":"操作成功","data":{"id":"tom"}}');
        echo $res;
    }

    public function testAnnotationValueValidRequire(): void
    {
        $_GET['id'] = 'sss';
        $request = new FpmRequest('/test/test/requireId', 'GET', []);
        $res = $this->router->dispatch($request);
        $this->expectOutputString('{"code":"000","success":true,"message":"操作成功","data":{"id":"sss"}}');
        echo $res;
    }

    public function testAnnotationRequireMethod(): void
    {
        $request = new FpmRequest('/test/test/get', 'POST', []);
        $this->expectException(RequestMethodException::class);
        $this->expectExceptionMessage('请求方法不对，需要是:GET');
        $this->router->dispatch($request);
    }

    public function testAnnotationRequireMethod2(): void
    {
        $request = new FpmRequest('/test/test/get', 'GET', []);
        $res = $this->router->dispatch($request);
        $this->expectOutputString('{"code":"000","success":true,"message":"操作成功"}');
        echo $res;
    }

    public function testAnnotationCreateValidateException(): void
    {
        $request = new FpmRequest('/test/test/createValidate', 'GET', []);
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('参数name缺少');
        $this->router->dispatch($request);
    }

    public function testAnnotationCreateValidateException2(): void
    {
        $_GET['name'] = 'tom';
        $request = new FpmRequest('/test/test/createValidate', 'GET', []);
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('参数age缺少');
        $this->router->dispatch($request);
    }

    public function testAnnotationUpdateValidateException2(): void
    {
        $_GET['name'] = 'tom';
        $request = new FpmRequest('/test/test/updateValidate', 'GET', []);
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('framework\tests\route\mock\updateValidate 验证类找不到');
        $this->router->dispatch($request);
    }

    public function testAnnotationTest3(): void
    {
        $_GET['name'] = 'tom';
        $_GET['age'] = 'age';
        $request = new FpmRequest('/test/test/test3', 'POST', []);
        $res = $this->router->dispatch($request);
        $this->expectOutputString('{"code":"000","success":true,"message":"操作成功","data":{"name":"tom","age":"age"}}');
        echo $res;
    }

}
