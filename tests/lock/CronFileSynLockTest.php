<?php

namespace framework\tests\lock;

use framework\lock\CronFileSynLock;
use PHPUnit\Framework\TestCase;

class CronFileSynLockTest extends TestCase
{
    public function setUp(): void
    {
        defined('RUNTIME_PATH') ?: define('RUNTIME_PATH', __DIR__ . '/runtime');
    }

    public function testLockAndUnlock(): void
    {
        $lockFile = '////sd//';
        $lock = new CronFileSynLock($lockFile);
        $ret = $lock->tryLock();
        $this->assertTrue($ret);
        $this->assertFileExists($lock->getLockFile());
        $this->assertFalse($lock->tryLock());
        $this->assertTrue($lock->unLock());
        $this->assertFileNotExists($lock->getLockFile());
        //again
        $this->assertTrue($lock->tryLock());
        $this->assertFileExists($lock->getLockFile());
        $this->assertTrue($lock->unLock());
        $this->assertFileNotExists($lock->getLockFile());
    }

    public function testUnlock(): void
    {
        $lockFile = 'asada/a/d';
        $lock = new CronFileSynLock($lockFile);
        $this->assertTrue($lock->unLock());
    }
}
