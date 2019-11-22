<?php

namespace Pop\Debug\Test;

use Pop\Debug\Storage;
use PHPUnit\Framework\TestCase;

class RedisTest extends TestCase
{

    public function testConstructor()
    {
        $redis = new Storage\Redis();
        $this->assertInstanceOf('Pop\Debug\Storage\Redis', $redis);
        $this->assertInstanceOf('Redis', $redis->redis());
        $this->assertNotEmpty($redis->getVersion());
    }

    public function testSave()
    {
        $redis = new Storage\Redis();
        $redis->save(123456, 'Hello World!');
        $this->assertTrue($redis->has(123456));
        $this->assertEquals('Hello World!', $redis->get(123456));
        $redis->save(123456, 'Hello World 2!');
        $this->assertTrue($redis->has(123456));
        $this->assertEquals('Hello World 2!', $redis->get(123456));
    }

    public function testEncodeException()
    {
        $this->expectException('Pop\Debug\Storage\Exception');
        $redis = new Storage\Redis();
        $redis->save(123456, ['Hello World!']);
    }

    public function testDelete()
    {
        $redis = new Storage\Redis();
        $this->assertTrue($redis->has(123456));
        $redis->delete(123456);
        $this->assertFalse($redis->has(123456));
    }

    public function testClear()
    {
        $redis = new Storage\Redis();
        $redis->clear();
        $this->assertFalse($redis->has(123456));
    }

    public function testSaveJson()
    {
        $redis = new Storage\Redis('JSON');
        $redis->save(123456, ['foo' => 'bar']);
        $this->assertTrue($redis->has(123456));
        $value = $redis->get(123456);
        $this->assertTrue(is_array($value));
        $this->assertTrue(isset($value['foo']));
        $this->assertEquals('bar', $value['foo']);
    }

    public function testSavePhp()
    {
        $redis = new Storage\Redis('PHP');
        $redis->save(123456, ['foo' => 'bar']);
        $this->assertTrue($redis->has(123456));
        $value = $redis->get(123456);
        $this->assertTrue(is_array($value));
        $this->assertTrue(isset($value['foo']));
        $this->assertEquals('bar', $value['foo']);
    }

}