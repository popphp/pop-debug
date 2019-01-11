<?php

namespace Pop\Debug\Test;

use Pop\Debug\Handler;
use PHPUnit\Framework\TestCase;

class MemoryHandlerTest extends TestCase
{

    public function testConstructor()
    {
        $handler = new Handler\MemoryHandler();
        $this->assertInstanceOf('Pop\Debug\Handler\MemoryHandler', $handler);
        $this->assertGreaterThan(0, (int)$handler->getLimit());
    }

    public function testUpdateMemoryUsage()
    {
        $handler = new Handler\MemoryHandler();
        $handler->updateMemoryUsage();
        $this->assertTrue($handler->hasUsages());

        $usage = array_values($handler->getUsages())[0];
        $this->assertGreaterThan(0, (int)$usage);
    }

    public function testUpdatePeakMemoryUsage()
    {
        $handler = new Handler\MemoryHandler();
        $handler->updatePeakMemoryUsage();
        $this->assertTrue($handler->hasPeakUsages());

        $peak = array_values($handler->getPeakUsages())[0];
        $this->assertGreaterThan(0, (int)$peak);
    }

    public function testFormatToString()
    {
        $handler = new Handler\MemoryHandler();
        $this->assertEquals('2GB', $handler->formatMemoryToString(2147483648));
        $this->assertEquals('512MB', $handler->formatMemoryToString(536870912));
        $this->assertEquals('512KB', $handler->formatMemoryToString(524288));
        $this->assertEquals('512B', $handler->formatMemoryToString(512));
    }


    public function testFormatToInt()
    {
        $handler = new Handler\MemoryHandler();
        $this->assertEquals(2147483648, $handler->formatMemoryToInt('2GB'));
        $this->assertEquals(536870912, $handler->formatMemoryToInt('512MB'));
        $this->assertEquals(524288, $handler->formatMemoryToInt('512KB'));
        $this->assertEquals(512, $handler->formatMemoryToInt('512B'));
    }

    public function testPrepare()
    {
        $handler = new Handler\MemoryHandler();
        $handler->updateMemoryUsage();
        $handler->updatePeakMemoryUsage();

        $data = $handler->prepare();

        $this->assertGreaterThan(0, (int)$data['limit']);
        $this->assertGreaterThan(0, (int)array_values($data['usages'])[0]);
        $this->assertGreaterThan(0, (int)array_values($data['peaks'])[0]);
    }

    public function testPrepareAsString()
    {
        $handler = new Handler\MemoryHandler();
        $handler->updateMemoryUsage();
        $handler->updatePeakMemoryUsage();

        $string = $handler->prepareHeaderAsString() . $handler->prepareAsString();

        $this->assertContains('Memory Handler', $string);
        $this->assertContains('Limit', $string);
    }

}