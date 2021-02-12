<?php

namespace Pop\Debug\Test;

use Pop\Debug\Handler;
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

        $exception = array_values($handler->getExceptions())[0];
        $this->assertEquals('Error: Whoops!', $exception->getMessage());
    }

    public function testPrepare()
    {
        $handler = new Handler\ExceptionHandler();
        $handler->addException(new \Exception('Error: Whoops!'));

        $data = $handler->prepare();

        $exception = array_values($data)[0];
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

}