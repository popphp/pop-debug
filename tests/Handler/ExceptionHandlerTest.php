<?php

namespace Pop\Debug\Test;

use Pop\Debug\Handler;
use Pop\Log;
use PHPUnit\Framework\TestCase;

class ExceptionHandlerTest extends TestCase
{

    public function testConstructor()
    {
        $handler = new Handler\ExceptionHandler();
        $this->assertInstanceOf('Pop\Debug\Handler\ExceptionHandler', $handler);
    }

    public function testAddException()
    {
        $handler = new Handler\ExceptionHandler();
        $handler->addException(new \Exception('Error: Whoops!'));
        $this->assertTrue($handler->hasExceptions());

        $exception = $handler->getExceptions()[0]['exception'];
        $this->assertEquals('Error: Whoops!', $exception->getMessage());
    }

    public function testPrepare()
    {
        $handler = new Handler\ExceptionHandler();
        $handler->addException(new \Exception('Error: Whoops!'));

        $data = $handler->prepare();

        $exception = $data[0]['exception'];
        $this->assertEquals('Error: Whoops!', $exception->getMessage());
    }

    public function testPrepareAsString()
    {
        $handler = new Handler\ExceptionHandler();
        $handler->addException(new \Exception('Error: Whoops!'));

        $string = $handler->prepareHeaderAsString() . $handler->prepareAsString();

        $this->assertStringContainsString('Exception Handler', $string);
        $this->assertStringContainsString('Error: Whoops!', $string);
    }

    public function testPrepareAsStringVerbose()
    {
        $handler = new Handler\ExceptionHandler(true);
        $handler->addException(new \Exception('Error: Whoops!'));

        $string = $handler->prepareHeaderAsString() . $handler->prepareAsString();

        $this->assertStringContainsString('Exception Handler', $string);
        $this->assertStringContainsString('Error: Whoops!', $string);
        $this->assertStringContainsString('Code:', $string);
    }

    public function testLog1()
    {
        $handler = new Handler\ExceptionHandler(false, 'exception',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::ERROR, 'context' => 'json']
        );
        $handler->addException(new \Exception('Whoops!'));
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLog2()
    {
        $handler = new Handler\ExceptionHandler(false, 'exception',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::ERROR, 'context' => 'json']
        );
        $handler->addException(new \Exception('Whoops 1!'));
        $handler->addException(new \Exception('Whoops 2!'));
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLogException()
    {
        $this->expectException('Pop\Debug\Handler\Exception');

        $handler = new Handler\ExceptionHandler(false, 'exception',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['foo' => 'test']
        );
        $handler->addException(new \Exception('Whoops 1!'));
        $handler->addException(new \Exception('Whoops 2!'));
        $handler->log();
    }

}
