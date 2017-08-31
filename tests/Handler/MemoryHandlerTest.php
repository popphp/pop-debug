<?php

namespace Pop\Debug\Test;

use Pop\Debug\Handler;

class MemoryHandlerTest extends \PHPUnit_Framework_TestCase
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