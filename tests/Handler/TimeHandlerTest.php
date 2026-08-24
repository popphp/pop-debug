<?php

namespace Pop\Debug\Test\Handler;

use Pop\Debug\Handler;
use Pop\Log;
use PHPUnit\Framework\TestCase;

class TimeHandlerTest extends TestCase
{

    public function testConstructor()
    {
        $handler = new Handler\TimeHandler('test1');
        $this->assertEquals('test1', $handler->getName());
        $handler->setName('test2');
        $this->assertInstanceOf('Pop\Debug\Handler\TimeHandler', $handler);
        $this->assertTrue($handler->hasName());
        $this->assertEquals('test2', $handler->getName());
        $this->assertTrue($handler->hasStart());
        $this->assertFalse($handler->hasElapsed());
    }

    public function testGetElapsed()
    {
        $handler = new Handler\TimeHandler();
        sleep(2);
        $handler->stop();
        $this->assertGreaterThan(0, $handler->getElapsed());
    }

    public function testPrepare1()
    {
        $handler = new Handler\TimeHandler();
        $handler->start();
        sleep(1);
        $handler->stop();
        $data = $handler->prepare();

        $this->assertGreaterThan(0, $data['start']);
        $this->assertGreaterThan(0, $data['end']);
    }

    public function testPrepare2()
    {
        $handler = new Handler\TimeHandler();
        $handler->start();
        sleep(1);
        $data = $handler->prepare();

        $this->assertGreaterThan(0, $data['start']);
        $this->assertGreaterThan(0, $data['end']);
    }

    public function testLog1()
    {
        $handler = new Handler\TimeHandler('time',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::INFO, 'context' => 'json']
        );
        $handler->stop();
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLog2()
    {
        $handler = new Handler\TimeHandler('time',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::WARNING, 'limit' => 1, 'context' => 'json']
        );
        sleep(2);
        $handler->stop();
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLogLimitExceededWithoutPriorStop()
    {
        $logFile = __DIR__ . '/../tmp/debug-limit-no-stop.log';
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        $handler = new Handler\TimeHandler('time',
            new Log\Logger(new Log\Writer\File($logFile)), ['level' => Log\Logger::WARNING, 'limit' => 1, 'context' => 'json']
        );
        sleep(2);
        // No stop()/prepare() call before log() - log() must compute elapsed time itself
        $handler->log();

        $this->assertStringContainsString('has been exceeded', file_get_contents($logFile));

        unlink($logFile);
    }

    public function testLogException()
    {
        $this->expectException('Pop\Debug\Handler\Exception');

        $handler = new Handler\TimeHandler('time',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['foo' => 'test']
        );
        $handler->stop();
        $handler->log();
    }

    public function testLogNoLogger()
    {
        $handler = new Handler\TimeHandler();
        $handler->stop();
        $handler->log();

        $this->assertFalse($handler->hasLogger());
    }

}
