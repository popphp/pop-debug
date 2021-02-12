<?php

namespace Pop\Debug\Test;

use Pop\Debug\Handler;
use PHPUnit\Framework\TestCase;

class TimeHandlerTest extends TestCase
{

    public function testConstructor()
    {
        $handler = new Handler\TimeHandler('test1');
        $this->assertEquals('test1', $handler->getName());
        $handler->setName('test2');
        $this->assertInstanceOf('Pop\Debug\Handler\TimeHandler', $handler);
        $this->assertEquals('test2', $handler->getName());
    }

    public function testStartAndStop()
    {
        $handler = new Handler\TimeHandler();
        $handler->start();
        $this->assertTrue($handler->hasStarted());
        $this->assertGreaterThan(0, $handler->getStart());
        $handler->stop();
        $this->assertTrue($handler->hasStopped());
        $this->assertGreaterThan(0, $handler->getStop());
    }

    public function testGetElapsed()
    {
        $handler = new Handler\TimeHandler(null, true);
        $this->assertGreaterThan(0, $handler->getElapsed());
    }

    public function testPrepare()
    {
        $handler = new Handler\TimeHandler();
        $handler->start();

        $data = $handler->prepare();

        $this->assertGreaterThan(0, $data['start']);
        $this->assertGreaterThan(0, $data['stop']);
    }

    public function testPrepareAsString()
    {
        $handler = new Handler\TimeHandler();
        $handler->start();

        $string = $handler->prepareHeaderAsString() . $handler->prepareAsString();

        $this->assertStringContainsString('Time Handler', $string);
        $this->assertStringContainsString('Start', $string);
    }

}