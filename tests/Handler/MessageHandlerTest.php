<?php

namespace Pop\Debug\Test\Handler;

use Pop\Debug\Handler;
use Pop\Log;
use PHPUnit\Framework\TestCase;

class MessageHandlerTest extends TestCase
{

    public function testConstructor()
    {
        $handler = new Handler\MessageHandler();
        $this->assertInstanceOf('Pop\Debug\Handler\MessageHandler', $handler);
    }

    public function testAddLogger()
    {
        $handler = new Handler\MessageHandler('message',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp')), ['level' => Log\Logger::NOTICE]
        );
        $this->assertTrue($handler->hasLogger());
        $this->assertTrue($handler->hasLoggingParams());
        $this->assertInstanceOf('Pop\Log\Logger', $handler->getLogger());
        $this->assertIsArray($handler->getLoggingParams());
    }

    public function testAddMessage()
    {
        $handler = new Handler\MessageHandler();
        $handler->addMessage('Test Message');
        $this->assertTrue($handler->hasMessages());

        $message = $handler->getMessages()[0]['message'];
        $this->assertEquals('Test Message', $message);
    }

    public function testPrepare()
    {
        $handler = new Handler\MessageHandler();
        $handler->addMessage('Test Message');

        $data = $handler->prepare();

        $message = $data[0]['message'];
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

    public function testLog1()
    {
        $handler = new Handler\MessageHandler('message',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::INFO, 'context' => 'json']
        );
        $handler->addMessage("Here is a message!");
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLog2()
    {
        $handler = new Handler\MessageHandler('message',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::INFO, 'context' => 'json']
        );
        $handler->addMessage("Here is a message!");
        $handler->addMessage("Here is another message!");
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLogException()
    {
        $this->expectException('Pop\Debug\Handler\Exception');

        $handler = new Handler\MessageHandler('message',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['foo' => 'test']
        );
        $handler->addMessage("Here is a message!");
        $handler->log();
    }

}
