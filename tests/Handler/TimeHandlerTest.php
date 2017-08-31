<?php

namespace Pop\Debug\Test;

use Pop\Debug\Handler;

class TimeHandlerTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $handler = new Handler\TimeHandler();
        $this->assertInstanceOf('Pop\Debug\Handler\TimeHandler', $handler);
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

        $this->assertContains('Time Handler', $string);
        $this->assertContains('Start', $string);
    }

}