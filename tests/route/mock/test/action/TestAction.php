<?php
namespace framework\tests\route\mock\test\action;

use framework\annotation\NotEmpty;
use framework\annotation\RequestMethod;
use framework\annotation\RequestValidate;
use framework\annotation\ValidRequire;
use framework\Controller;
use framework\request\RequestInterface;
use framework\util\Result;

class TestAction extends Controller
{
    /**
     * @ValidRequire(name="id", msg="请传入id")
     * @param string $id
     * @return Result
     * date 2021/2/1
     */
    public function requireId(string $id): Result
    {
        return Result::ok()->data(['id' => $id]);
    }

    /**
     * @ValidRequire(name="id", msg="请传入id")
     * @ValidRequire(name="name", msg="请传入name")
     * @param string $id
     * @param string $name
     * @return Result
     * date 2021/2/4
     */
    public function requireMore(string $id, string $name): Result
    {
        return Result::ok()->data(['id' => $id, 'name' => $name]);
    }

    /**
     * @NotEmpty(name="id", msg="id不能为空")
     * @param string $id
     * @return Result
     * date 2021/2/19
     */
    public function requireIdNotEmpty(string $id): Result
    {
        return Result::ok()->data(['id' => $id]);
    }

    /**
     * @RequestMethod(method="GET")
     * @return Result
     * date 2021/2/1
     */
    public function get(): Result
    {
        return Result::ok();
    }

    /**
     * @RequestValidate(validate="framework\tests\route\mock\UserValidator", scene="create")
     * @return Result
     * date 2021/2/1
     */
    public function createValidate(): Result
    {
        return Result::error();
    }

    /**
     * @RequestValidate(validate="framework\tests\route\mock\updateValidate", scene="update")
     * @return Result
     * date 2021/2/1
     */
    public function updateValidate(): Result
    {
        return Result::error();
    }

    /**
     * @ValidRequire(name="name", msg="请传入name")
     * @RequestMethod(method="POST")
     * @RequestValidate(validate="framework\tests\route\mock\UserValidator", scene="create")
     * @param RequestInterface $request
     * @param string $name
     * @return Result
     * date 2021/2/1
     */
    public function test3(RequestInterface $request, string $name): Result
    {
        return Result::ok()->data(['name' => $name, 'age' => $request->getParameter('age')]);
    }
}
