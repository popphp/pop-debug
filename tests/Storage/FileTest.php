<?php

namespace Pop\Debug\Test;

use Pop\Debug\Storage;

class FileTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $file = new Storage\File(__DIR__ . '/../tmp');
        $this->assertInstanceOf('Pop\Debug\Storage\File', $file);
        $this->assertEquals(realpath(__DIR__ . '/../tmp'), $file->getDir());
        $this->assertEquals('text', $file->getFormat());
        $this->assertTrue($file->isText());
        $this->assertFalse($file->isJson());
        $this->assertFalse($file->isPhp());

    }

    public function testSetDirException()
    {
        $this->expectException('Pop\Debug\Storage\Exception');
        $file = new Storage\File(__DIR__ . '/../bad');
    }

    public function testSaveAndGetText()
    {
        $time = time();
        $file = new Storage\File(__DIR__ . '/../tmp');
        $file->save($time, 'Hello World');
        $this->assertTrue($file->has($time));
        $this->assertEquals('Hello World', $file->get($time));
        $file->delete($time);
    }

    public function testSaveAndGetJson()
    {
        $time = time();
        $file = new Storage\File(__DIR__ . '/../tmp', 'json');
        $file->save($time, 'Hello World');
        $this->assertTrue($file->has($time));
        $this->assertEquals('Hello World', $file->get($time));
        $file->delete($time);
    }

    public function testSaveAndGetPhp()
    {
        $time = time();
        $file = new Storage\File(__DIR__ . '/../tmp', 'php');
        $file->save($time, 'Hello World');
        $this->assertTrue($file->has($time));
        $this->assertEquals('Hello World', $file->get($time));
        $file->delete($time);
    }

    public function testSaveAndGetTextException()
    {
        $this->expectException('Pop\Debug\Storage\Exception');
        $time = time();
        $file = new Storage\File(__DIR__ . '/../tmp');
        $file->save($time, ['Hello World']);
    }

    public function testClear()
    {
        $time = time();
        $file = new Storage\File(__DIR__ . '/../tmp');
        $file->save($time, 'Hello World');
        $this->assertFileExists(__DIR__ . '/../tmp/' . $time . '.log');
        $file->clear();
        $this->assertFileNotExists(__DIR__ . '/../tmp/' . $time . '.log');
        touch(__DIR__ . '/../tmp/.empty');
    }

}