<?php
/**
 * This file is part of Monda-PHP.
 *
 */
namespace framework\lock;

interface ISynLock
{
    /**
     * 获取同步锁
     */
    public function tryLock(): bool;

    /**
     * 解锁
     */
    public function unLock(): bool;
}
