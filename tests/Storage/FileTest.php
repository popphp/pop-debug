<?php

namespace Pop\Debug\Test\Storage;

use Pop\Debug;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{

    public function testConstructor()
    {
        $file = new Debug\Storage\File(__DIR__ . '/../tmp');
        $this->assertInstanceOf('Pop\Debug\Storage\File', $file);
        $this->assertEquals(realpath(__DIR__ . '/../tmp'), $file->getDir());
        $this->assertEquals('TEXT', $file->getFormat());
        $this->assertFalse($file->isJson());
        $this->assertFalse($file->isPhp());

    }

    public function testSetDirException()
    {
        $this->expectException('Pop\Debug\Storage\Exception');
        $file = new Debug\Storage\File(__DIR__ . '/../bad');
    }

    public function testSaveAndGetText()
    {
        $time = time();
        $file = new Debug\Storage\File(__DIR__ . '/../tmp');
        $file->save($time, 'Hello World');
        $this->assertTrue($file->has($time));
        $this->assertEquals('Hello World', $file->getById($time));
        $this->assertIsArray($file->getById($time . '*'));
        $file->delete($time);
    }

    public function testSaveAndGetJson()
    {
        $time = time();
        $file = new Debug\Storage\File(__DIR__ . '/../tmp', 'json');
        $file->save($time, 'Hello World');
        $this->assertTrue($file->has($time));
        $this->assertEquals('Hello World', $file->getById($time));
        $file->delete($time);
    }

    public function testSaveAndGetPhp()
    {
        $time = time();
        $file = new Debug\Storage\File(__DIR__ . '/../tmp', 'php');
        $file->save($time, 'Hello World');
        $this->assertTrue($file->has($time));
        $this->assertEquals('Hello World', $file->getById($time));
        $file->delete($time);
    }

    public function testSaveAndGetByType()
    {
        $time = time();
        $file = new Debug\Storage\File(__DIR__ . '/../tmp');
        $file->save($time . '-message', 'Hello World');
        $this->assertIsArray($file->getByType('message'));
        $file->delete($time . '-message');
    }

    public function testSaveAndGetTextException()
    {
        $this->expectException('Pop\Debug\Storage\Exception');
        $time = time();
        $file = new Debug\Storage\File(__DIR__ . '/../tmp');
        $file->save($time, ['Hello World']);
    }

    public function testClear()
    {
        $time = time();
        $file = new Debug\Storage\File(__DIR__ . '/../tmp');
        $file->save($time, 'Hello World');
        $this->assertFileExists(__DIR__ . '/../tmp/' . $time . '.log');
        $file->clear();
        $this->assertFileDoesNotExist(__DIR__ . '/../tmp/' . $time . '.log');
        touch(__DIR__ . '/../tmp/.empty');
    }

}
