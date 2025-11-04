<?php

namespace Pop\Debug\Test\Storage;

use Pop\Debug;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{

    public function testConstructor()
    {
        $file = new Debug\Storage\File(__DIR__ . '/../tmp');
        $this->assertInstanceOf('Pop\Debug\Storage\File', $file);
        $this->assertEquals(realpath(__DIR__ . '/../tmp'), $file->getDir());
    }

    public function testSetDirException()
    {
        $this->expectException('Pop\Debug\Storage\Exception');
        $file = new Debug\Storage\File(__DIR__ . '/../bad');
    }

    public function testPrepareEvents()
    {
        $debugger = new Debug\Debugger();
        $debugger->addHandlers([
            new Debug\Handler\ExceptionHandler(true),
            new Debug\Handler\MessageHandler(),
            new Debug\Handler\MemoryHandler(),
            new Debug\Handler\MemoryHandler(),
            new Debug\Handler\TimeHandler(),
        ]);
        $debugger->setStorage(new Debug\Storage\File(__DIR__ . '/../tmp'));

        $debugger['exception']->addException(new \Exception('Hey! Something happened!'));
        $debugger['message']->addMessage('Hey! Something happened!');
        $debugger['memory']->updateMemoryUsage();
        $debugger['memory']->updatePeakMemoryUsage();
        sleep(2);

        $events = [];

        foreach ($debugger->getHandlers() as $name => $handler) {
            $events = array_merge($events, $debugger->getStorage()->prepareEvents($debugger->getRequestId(), $name, $handler));
        }

        $this->assertCount(6, $events);


    }

}
