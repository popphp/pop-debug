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

    public function testSetDirNotWritableException()
    {
        $dir = __DIR__ . '/../tmp/unwritable';
        mkdir($dir);
        chmod($dir, 0500);

        if (is_writable($dir)) {
            // Running as a user (e.g. root) that ignores permission bits - nothing to assert.
            rmdir($dir);
            $this->markTestSkipped('Current user can write regardless of permission bits.');
        }

        try {
            $this->expectException('Pop\Debug\Storage\Exception');
            new Debug\Storage\File($dir);
        } finally {
            chmod($dir, 0777);
            rmdir($dir);
        }
    }

    public function testHasDir()
    {
        $file = new Debug\Storage\File(__DIR__ . '/../tmp');
        $this->assertTrue($file->hasDir());
    }

    public function testClearOnMissingDir()
    {
        $dir = __DIR__ . '/../tmp/clear-missing';
        mkdir($dir);
        $file = new Debug\Storage\File($dir);
        rmdir($dir);

        // opendir() on a now-missing dir fails silently (@-suppressed); clear() should just no-op.
        $file->clear();
        $this->addToAssertionCount(1);
    }

    public function testSetFormatException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Debug\Storage\File(__DIR__ . '/../tmp', 'xml');
    }

    public function testSaveNdJson()
    {
        $file = new Debug\Storage\File(__DIR__ . '/../tmp', 'ndjson');
        $this->assertEquals('ndjson', $file->getFormat());

        $debugger = new Debug\Debugger();
        $debugger->addHandler(new Debug\Handler\MemoryHandler());
        $debugger->setStorage($file);
        $debugger['memory']->updateUsage();

        $requestId = $debugger->save();
        $filename  = __DIR__ . '/../tmp/' . $requestId . '-memory.ndjson';

        $this->assertFileExists($filename);

        $lines = array_values(array_filter(explode(PHP_EOL, file_get_contents($filename))));
        $this->assertCount(3, $lines);

        // The 'limit' row carries a json_encode()'d context; it must come back as a real
        // nested array/object in the NDJSON output, not a doubly-escaped JSON string.
        $limitEvent = json_decode($lines[0], true);
        $this->assertIsArray($limitEvent);
        $this->assertEquals('memory', $limitEvent['handler']);
        $this->assertEquals('limit', $limitEvent['type']);
        $this->assertIsArray($limitEvent['context']);
        $this->assertArrayHasKey('limit', $limitEvent['context']);

        unlink($filename);
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
