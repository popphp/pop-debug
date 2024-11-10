<?php

namespace Pop\Debug\Test\Handler;

use Pop\Debug\Handler;
use Pop\Log;
use PHPUnit\Framework\TestCase;

class TimeHandlerTest extends TestCase
{

    public function testConstructor()
    {
        $handler = new Handler\TimeHandler(true, 'test1');
        $this->assertEquals('test1', $handler->getName());
        $handler->setName('test2');
        $this->assertInstanceOf('Pop\Debug\Handler\TimeHandler', $handler);
        $this->assertTrue($handler->hasName());
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
        $handler = new Handler\TimeHandler();
        sleep(2);
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

    public function testLog1()
    {
        $handler = new Handler\TimeHandler(true, 'time',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::INFO]
        );
        $handler->stop();
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLog2()
    {
        $handler = new Handler\TimeHandler(true, 'time',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::WARNING, 'limit' => 1]
        );
        sleep(2);
        $handler->stop();
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLogException()
    {
        $this->expectException('Pop\Debug\Handler\Exception');

        $handler = new Handler\TimeHandler(true, 'time',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['foo' => 'test']
        );
        $handler->stop();
        $handler->log();
    }

}
