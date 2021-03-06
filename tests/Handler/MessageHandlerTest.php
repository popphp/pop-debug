<?php

namespace Pop\Debug\Test;

use Pop\Debug\Handler;
use PHPUnit\Framework\TestCase;

class MessageHandlerTest extends TestCase
{

    public function testConstructor()
    {
        $handler = new Handler\MessageHandler();
        $this->assertInstanceOf('Pop\Debug\Handler\MessageHandler', $handler);
    }

    public function testAddMessage()
    {
        $handler = new Handler\MessageHandler();
        $handler->addMessage('Test Message');
        $this->assertTrue($handler->hasMessages());

        $message = array_values($handler->getMessages())[0];
        $this->assertEquals('Test Message', $message);
    }

    public function testPrepare()
    {
        $handler = new Handler\MessageHandler();
        $handler->addMessage('Test Message');

        $data = $handler->prepare();

        $message = array_values($data)[0];
        $this->assertEquals('Test Message', $message);
    }

    public function testPrepareAsString()
    {
        $handler = new Handler\MessageHandler();
        $handler->addMessage('Test Message');

        $string = $handler->prepareHeaderAsString() . $handler->prepareAsString();

        $this->assertStringContainsString('Message Handler', $string);
        $this->assertStringContainsString('Test Message', $string);
    }

}