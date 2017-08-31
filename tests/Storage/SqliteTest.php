<?php

namespace Pop\Debug\Test;

use Pop\Debug\Storage;

class SqliteTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        chmod(__DIR__ . '/../tmp', 0777);
        touch(__DIR__ . '/../tmp/debug.sqlite');
        chmod(__DIR__ . '/../tmp/debug.sqlite', 0777);
        $sqlite = new Storage\Sqlite(__DIR__ . '/../tmp/debug.sqlite');
        $this->assertInstanceOf('Pop\Debug\Storage\Sqlite', $sqlite);
        $this->assertEquals(__DIR__ . '/../tmp/debug.sqlite', $sqlite->getDb());
        $this->assertEquals('pop_debug', $sqlite->getTable());
    }

    public function testSaveAndGetText()
    {
        $time = time();
        $sqlite = new Storage\Sqlite(__DIR__ . '/../tmp/debug.sqlite');
        $sqlite->save($time, 'Hello World');
        $this->assertTrue($sqlite->has($time));
        $this->assertEquals('Hello World', $sqlite->get($time));
        $sqlite->delete($time);
    }

    public function testSaveAndGetJson()
    {
        $time = time();
        $sqlite = new Storage\Sqlite(__DIR__ . '/../tmp/debug.sqlite');
        $sqlite->save($time, 'Hello World');
        $this->assertTrue($sqlite->has($time));
        $this->assertEquals('Hello World', $sqlite->get($time));
        $sqlite->delete($time);
    }

    public function testSaveAndGetPhp()
    {
        $time = time();
        $sqlite = new Storage\Sqlite(__DIR__ . '/../tmp/debug.sqlite');
        $sqlite->save($time, 'Hello World');
        $this->assertTrue($sqlite->has($time));
        $this->assertEquals('Hello World', $sqlite->get($time));
        $sqlite->delete($time);
    }

    public function testSaveAndGetTextWithPdo()
    {
        $time = time();
        $sqlite = new Storage\Sqlite(__DIR__ . '/../tmp/debug.sqlite', 'json', 'pop_debug', true);
        $sqlite->save($time, 'Hello World');
        $this->assertTrue($sqlite->has($time));
        $this->assertEquals('Hello World', $sqlite->get($time));
        $sqlite->delete($time);
    }

    public function testSaveAndGetJsonWithPdo()
    {
        $time = time();
        $sqlite = new Storage\Sqlite(__DIR__ . '/../tmp/debug.sqlite', 'json', 'pop_debug', true);
        $sqlite->save($time, 'Hello World');
        $this->assertTrue($sqlite->has($time));
        $this->assertEquals('Hello World', $sqlite->get($time));
        $sqlite->delete($time);
    }

    public function testSaveAndGetPhpWithPdo()
    {
        $time = time();
        $sqlite = new Storage\Sqlite(__DIR__ . '/../tmp/debug.sqlite', 'json', 'pop_debug', true);
        $sqlite->save($time, 'Hello World');
        $this->assertTrue($sqlite->has($time));
        $this->assertEquals('Hello World', $sqlite->get($time));
        $sqlite->delete($time);
    }

    public function testClear()
    {
        $time = time();
        $sqlite = new Storage\Sqlite(__DIR__ . '/../tmp/debug.sqlite');
        $sqlite->save($time, 'Hello World');
        $this->assertTrue($sqlite->has($time));
        $sqlite->clear();
        $this->assertFalse($sqlite->has($time));

        if (file_exists(__DIR__ . '/../tmp/debug.sqlite')) {
            unlink(__DIR__ . '/../tmp/debug.sqlite');
        }
    }

}