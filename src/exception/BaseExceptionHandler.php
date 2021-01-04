<?php
/**
 * This file is part of Monda-PHP.
 *
 */
namespace framework\exception;

use Throwable;

/**
 * Class BaseExceptionHandler.
 */
class BaseExceptionHandler
{
    //需要忽略日志异常，不记录在日志上
    protected $ignores = [];

    /**
     * 初始化.
     */
    public function init(): void
    {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
    }

    /**
     * 是否忽略异常.
     * @param Throwable $e
     * @return bool
     */
    protected function isIgnore(Throwable $e): bool
    {
        foreach ($this->ignores ?? [] as $clazz) {
            if ($clazz === get_class($e)) {
                return true;
            }
        }
        return false;
    }
}
