<?php


namespace framework\tests\Container\mock;


use framework\exception\BaseExceptionHandler;
use Throwable;

class MockHandleException extends BaseExceptionHandler
{
    protected $ignores = [
    ];

    /**
     * @param Throwable $e
     *                     异常托管到这个方法
     */
    public function handleException(Throwable $e): void
    {}
}