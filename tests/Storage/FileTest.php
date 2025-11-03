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

    }

    public function testSetDirException()
    {
        $this->expectException('Pop\Debug\Storage\Exception');
        $file = new Debug\Storage\File(__DIR__ . '/../bad');
    }

}
