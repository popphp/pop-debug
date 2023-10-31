<?php

namespace Pop\Debug\Test;

use Pop\Db\Db;
use Pop\Debug\Storage;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{

    public function testConstructor()
    {
        chmod(__DIR__ . '/../tmp', 0777);
        touch(__DIR__ . '/../tmp/debug.sqlite');
        chmod(__DIR__ . '/../tmp/debug.sqlite', 0777);
        $db = new Storage\Database(Db::sqliteConnect(['database' => __DIR__ . '/../tmp/debug.sqlite']));
        $this->assertInstanceOf('Pop\Debug\Storage\Database', $db);
        $this->assertInstanceOf('Pop\Db\Adapter\Sqlite', $db->getDb());
        $this->assertEquals('pop_debug', $db->getTable());
    }

    public function testSave()
    {
        $db = new Storage\Database(Db::sqliteConnect(['database' => __DIR__ . '/../tmp/debug.sqlite']));
        $db->save(123456, 'Hello World!');
        $this->assertTrue($db->has(123456));
        $this->assertEquals('Hello World!', $db->get(123456));
        $db->save(123456, 'Hello World 2!');
        $this->assertTrue($db->has(123456));
        $this->assertEquals('Hello World 2!', $db->get(123456));
    }

    public function testEncodeException()
    {
        $this->expectException('Pop\Debug\Storage\Exception');
        $db = new Storage\Database(Db::sqliteConnect(['database' => __DIR__ . '/../tmp/debug.sqlite']));
        $db->save(123456, ['Hello World!']);
    }

    public function testDelete()
    {
        $db = new Storage\Database(Db::sqliteConnect(['database' => __DIR__ . '/../tmp/debug.sqlite']));
        $this->assertTrue($db->has(123456));
        $db->delete(123456);
        $this->assertFalse($db->has(123456));
    }

    public function testClear()
    {
        $db = new Storage\Database(Db::sqliteConnect(['database' => __DIR__ . '/../tmp/debug.sqlite']));
        $db->clear();
        $this->assertFalse($db->has(123456));
    }

    public function testSaveJson()
    {
        $db = new Storage\Database(Db::sqliteConnect(['database' => __DIR__ . '/../tmp/debug.sqlite']), 'JSON');
        $db->save(123456, ['foo' => 'bar']);
        $this->assertTrue($db->has(123456));
        $value = $db->get(123456);
        $this->assertTrue(is_array($value));
        $this->assertTrue(isset($value['foo']));
        $this->assertEquals('bar', $value['foo']);
    }

    public function testSavePhp()
    {
        $db = new Storage\Database(Db::sqliteConnect(['database' => __DIR__ . '/../tmp/debug.sqlite']), 'PHP');
        $db->save(123456, ['foo' => 'bar']);
        $this->assertTrue($db->has(123456));
        $value = $db->get(123456);
        $this->assertTrue(is_array($value));
        $this->assertTrue(isset($value['foo']));
        $this->assertEquals('bar', $value['foo']);
        unlink(__DIR__ . '/../tmp/debug.sqlite');
    }

}