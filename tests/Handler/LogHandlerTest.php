<?php

namespace Pop\Debug\Test;

use Pop\Debug\Handler;
use Pop\Log;
use PHPUnit\Framework\TestCase;

class LogHandlerTest extends TestCase
{

    public function testConstructor()
    {
        $logger  = new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/system.log'));
        $handler = new Handler\LogHandler($logger);
        $this->assertInstanceOf('Pop\Debug\Handler\LogHandler', $handler);
        $this->assertInstanceOf('Pop\Log\Logger', $handler->getLogger());
        if (file_exists(__DIR__ . '/../tmp/system.log')) {
            unlink(__DIR__ . '/../tmp/system.log');
        }
    }

    public function testLog()
    {
        $logger  = new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/system.log'));
        $handler = new Handler\LogHandler($logger);

        $handler->log(Log\Logger::ALERT, 'This is an alert.');
        sleep(0.1);
        $handler->emergency('This is an emergency.');
        sleep(0.1);
        $handler->alert('This is an alert #2.');
        sleep(0.1);
        $handler->critical('This is a critical warning.');
        sleep(0.1);
        $handler->error('This is an error.');
        sleep(0.1);
        $handler->warning('This is a warning.');
        sleep(0.1);
        $handler->notice('This is a notice.');
        sleep(0.1);
        $handler->info('This is an info.');
        sleep(0.1);
        $handler->debug('This is a debug.');
        sleep(0.1);

        $log = file_get_contents(__DIR__ . '/../tmp/system.log');
        $this->assertContains('This is an alert', $log);
        $this->assertContains('This is an emergency.', $log);
        $this->assertContains('This is an alert #2.', $log);
        $this->assertContains('This is a critical warning.', $log);
        $this->assertContains('This is an error.', $log);
        $this->assertContains('This is a warning.', $log);
        $this->assertContains('This is a notice.', $log);
        $this->assertContains('This is an info.', $log);
        $this->assertContains('This is a debug.', $log);
        if (file_exists(__DIR__ . '/../tmp/system.log')) {
            unlink(__DIR__ . '/../tmp/system.log');
        }
    }

    public function testPrepare()
    {
        $logger  = new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/system.log'));
        $handler = new Handler\LogHandler($logger);
        $handler->info('Test Log');

        $data = $handler->prepare();

        $message = array_values($data)[0];
        $this->assertEquals("INFO\tTest Log", $message);
        if (file_exists(__DIR__ . '/../tmp/system.log')) {
            unlink(__DIR__ . '/../tmp/system.log');
        }
    }

    public function testPrepareAsString()
    {
        $logger  = new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/system.log'));
        $handler = new Handler\LogHandler($logger);
        $handler->info('Test Log');

        $string = $handler->prepareHeaderAsString() . $handler->prepareAsString();

        $this->assertContains('Log Handler', $string);
        $this->assertContains('Test Log', $string);
        if (file_exists(__DIR__ . '/../tmp/system.log')) {
            unlink(__DIR__ . '/../tmp/system.log');
        }
    }

}