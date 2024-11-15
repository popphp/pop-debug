<?php

namespace Pop\Debug\Test;

use Pop\Debug\Debugger;
use Pop\Debug\Handler;
use Pop\Debug\Storage;
use Pop\Log;
use PHPUnit\Framework\TestCase;

class DebuggerTest extends TestCase
{

    public function testConstructor1()
    {
        $debugger = new Debugger([
            new Handler\ExceptionHandler(),
            new Storage\File(__DIR__ . '/tmp')
        ]);
        $this->assertInstanceOf('Pop\Debug\Debugger', $debugger);
        $this->assertTrue($debugger->hasHandler('exception'));
        $this->assertTrue($debugger->hasStorage());
        $this->assertInstanceOf('Pop\Debug\Storage\File', $debugger->getStorage());
    }

    public function testConstructor2()
    {
        $debugger = new Debugger(
            new Handler\ExceptionHandler(),
            new Storage\File(__DIR__ . '/tmp')
        );
        $this->assertInstanceOf('Pop\Debug\Debugger', $debugger);
        $this->assertTrue($debugger->hasHandler('exception'));
        $this->assertTrue($debugger->hasStorage());
    }

    public function testAddHandler()
    {
        $exception = new Handler\ExceptionHandler(false, 'custom');
        $debugger = new Debugger();
        $debugger->addHandlers([$exception]);

        $handlerName = null;

        foreach ($debugger as $name => $handler) {
            $handlerName = $name;
        }

        $this->assertEquals('custom-exception', $handlerName);
        $this->assertTrue($debugger->hasHandler('custom-exception'));
        $this->assertTrue(isset($debugger['custom-exception']));
        $this->assertEquals(1, count($debugger->getHandlers()));
        $this->assertEquals(1, count($debugger));
        $this->assertInstanceOf('Pop\Debug\Handler\ExceptionHandler', $debugger->getHandler('custom-exception'));
        $this->assertInstanceOf('Pop\Debug\Handler\ExceptionHandler', $debugger['custom-exception']);
    }

    public function testSetHandler()
    {
        $exception = new Handler\ExceptionHandler('custom');
        $otherException = new Handler\ExceptionHandler('other');
        $debugger = new Debugger();
        $debugger['custom-exception'] = $exception;
        $debugger->{'other-exception'} = $otherException;
        $this->assertTrue($debugger->hasHandler('custom-exception'));
        $this->assertTrue(isset($debugger['custom-exception']));
        $this->assertTrue(isset($debugger->{'custom-exception'}));
        $this->assertEquals(2, count($debugger->getHandlers()));
        $this->assertInstanceOf('Pop\Debug\Handler\ExceptionHandler', $debugger->getHandler('custom-exception'));
        $this->assertInstanceOf('Pop\Debug\Handler\ExceptionHandler', $debugger['custom-exception']);
        $this->assertInstanceOf('Pop\Debug\Handler\ExceptionHandler', $debugger->{'custom-exception'});
        unset($debugger->{'custom-exception'});
        $this->assertFalse($debugger->hasHandler('custom-exception'));
    }

    public function testAddLogger()
    {
        $debugger = new Debugger(
            new Handler\ExceptionHandler(),
            new Storage\File(__DIR__ . '/tmp')
        );
        $debugger->addLogger(new Log\Logger(new Log\Writer\File(__DIR__ . '/tmp')), ['level' => Log\Logger::NOTICE]);
        $this->assertTrue($debugger['exception']->hasLogger());
    }

    public function testSetHandlerException()
    {
        $this->expectException('Pop\Debug\Exception');
        $debugger = new Debugger();
        $debugger['custom-exception'] = ['bad'];
    }

    public function testGetData()
    {
        $debugger = new Debugger([
            new Handler\MemoryHandler(),
            new Storage\File(__DIR__ . '/tmp')
        ]);
        $debugger['memory']->updatePeakMemoryUsage();

        $data = $debugger->getData();
        $this->assertTrue(isset($data['memory']));
    }

    public function testSave()
    {
        $debugger = new Debugger([
            new Handler\MemoryHandler(),
            new Storage\File(__DIR__ . '/tmp')
        ]);
        $debugger['memory']->updatePeakMemoryUsage();
        $debugger->save();

        $dh = @opendir(__DIR__ . '/tmp');

        while (false !== ($obj = readdir($dh))) {
            if (($obj != '.') && ($obj != '..') && ($obj != '.empty') &&
                !is_dir(__DIR__ . '/tmp' . DIRECTORY_SEPARATOR . $obj) && is_file(__DIR__ . '/tmp' . DIRECTORY_SEPARATOR . $obj)) {
                $this->assertStringContainsString('Usage', file_get_contents(__DIR__ . '/tmp' . DIRECTORY_SEPARATOR . $obj));
                break;
            }
        }

        if (file_exists(__DIR__ . '/tmp' . DIRECTORY_SEPARATOR . $obj)) {
            unlink(__DIR__ . '/tmp' . DIRECTORY_SEPARATOR . $obj);
        }
    }

    public function testGet()
    {
        $debugger = new Debugger([
            new Handler\MemoryHandler(),
            new Storage\File(__DIR__ . '/tmp')
        ]);
        $debugger['memory']->updatePeakMemoryUsage();
        $requestId = $debugger->save();

        $this->assertIsArray($debugger->getByType('memory'));
        $this->assertTrue($debugger->has($requestId . '-memory'));
        $this->assertTrue(str_contains($debugger->getById($requestId . '-memory'), 'Usage'));
        $debugger->delete($requestId . '-memory');
        $debugger->clear();
        $this->assertFalse($debugger->has($requestId . '-memory'));
    }

    public function testRender()
    {
        $debugger = new Debugger([
            new Handler\MemoryHandler(),
            new Storage\File(__DIR__ . '/tmp')
        ]);
        $debugger['memory']->updatePeakMemoryUsage();

        $data = $debugger->render();
        $this->assertStringContainsString('Usage', $data);
    }

    public function testRenderWithHeaders()
    {
        $debugger = new Debugger([
            new Handler\MemoryHandler(),
            new Storage\File(__DIR__ . '/tmp')
        ]);
        $debugger['memory']->updatePeakMemoryUsage();

        $data = $debugger->renderWithHeaders();
        $this->assertStringContainsString('Memory Handler', $data);
    }

    public function testRenderToString()
    {
        $debugger = new Debugger([
            new Handler\MemoryHandler(),
            new Storage\File(__DIR__ . '/tmp')
        ]);
        $debugger['memory']->updatePeakMemoryUsage();

        ob_start();
        echo $debugger;
        $results = ob_get_clean();

        $this->assertStringContainsString('Memory Handler', $results);
    }

}
