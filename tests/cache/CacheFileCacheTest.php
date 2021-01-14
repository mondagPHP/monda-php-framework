<?php
namespace framework\tests\cache;

use framework\cache\FileCache;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    /** @var $fileCache FileCache */
    private $fileCache;
    private $dir = __DIR__ . '/../../runtime/cache/';
    private $mode = 0766;

    public static function tearDownAfterClass(): void
    {
        $dir = __DIR__ . '/../../runtime/cache/';
        foreach (scandir($dir) ?: [] as $file) {
            fwrite(STDOUT, $dir . $file);
            if (! is_dir($file)) {
                unlink($dir . $file);
            }
        }
    }

    protected function setUp(): void
    {
        $this->fileCache = new FileCache($this->dir, $this->mode);
    }

    public function testSet(): void
    {
        $ret = $this->fileCache->set('name', 'tom');
        $this->assertTrue($ret);
        $this->assertFileExists($this->dir . 'name.cache');
    }

    public function testGet(): void
    {
        $value = $this->fileCache->get('nameGet');
        $this->assertNull($value);
        $default = $this->fileCache->get('nameGet', 'default');
        $this->assertEquals('default', $default);
    }

    public function testGetAndSet(): void
    {
        $this->fileCache->set('getAndSet', 'getAndSet');
        $value = $this->fileCache->get('getAndSet');
        $this->assertEquals('getAndSet', $value);
    }

    public function testDelete(): void
    {
        $ret = $this->fileCache->delete('del');
        $this->assertTrue($ret);
    }

    public function testSetAndDelete(): void
    {
        $this->fileCache->set('testSetAndDel', 'testSetAndDel');
        $ret = $this->fileCache->delete('testSetAndDel');
        $this->assertTrue($ret);
        $this->assertFileNotExists($this->dir . 'testSetAndDel.cache');
    }

    public function testRemember():void
    {
        $value = $this->fileCache->remember('testRemember', 1, function () {
            return 'testRemember';
        });
        $this->assertEquals('testRemember', $value);
    }

    public function testSetExpire(): void
    {
        $ret = $this->fileCache->set('name2', 'tome', 1);
        $this->assertTrue($ret);
        sleep(2);
        $ret = $this->fileCache->get('name2');
        $this->assertNull($ret);
    }

    public function testSetObject(): void
    {
        $obj = new Person();
        $this->assertTrue($this->fileCache->set('testSetObject', $obj));
        $cacheObj = $this->fileCache->get('testSetObject');
        $this->assertEquals('tom', $cacheObj->name);
        $this->assertEquals('tom', $cacheObj->getName());
    }

    public function testClean(): void
    {
        $ret = $this->fileCache->set('testClean', 'tom', 1);
        sleep(2);
        $this->fileCache->clean($this->dir);
        $this->assertFileNotExists($this->dir . 'testClean.cache');
    }
}

class Person
{
    public $name = 'tom';

    public function getName(): string
    {
        return $this->name;
    }
}
