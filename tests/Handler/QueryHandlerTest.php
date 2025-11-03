<?php

namespace Pop\Debug\Test\Handler;

use Pop\Debug\Handler;
use Pop\Db\Adapter\Profiler;
use Pop\Log;
use PHPUnit\Framework\TestCase;

class QueryHandlerTest extends TestCase
{

    public function testConstructor()
    {
        $profiler = new Profiler\Profiler();
        $handler  = new Handler\QueryHandler($profiler);
        $this->assertInstanceOf('Pop\Debug\Handler\QueryHandler', $handler);
        $profiler->addStep();
        $profiler->current->setQuery('SELECT * FROM users');
        $profiler->current->finish();
        $this->assertTrue($handler->hasProfiler());
        $this->assertInstanceOf('Pop\Db\Adapter\Profiler\Profiler', $handler->getProfiler());
        $this->assertInstanceOf('Pop\Db\Adapter\Profiler\Profiler', $handler->profiler());
        $this->assertInstanceOf('Pop\Db\Adapter\Profiler\Profiler', $handler->profiler);
    }

    public function testPrepare()
    {
        $profiler = new Profiler\Profiler();
        $handler  = new Handler\QueryHandler($profiler);
        $profiler->addStep();
        $profiler->current->setQuery('SELECT * FROM users');
        $profiler->current->finish();
        $data = $handler->prepare();
        $this->assertEquals(1, count($data['steps']));
    }

    public function testLog1()
    {
        $profiler = new Profiler\Profiler();
        $handler = new Handler\QueryHandler($profiler, 'query',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::INFO, 'context' => 'json']
        );
        $profiler->addStep();
        $profiler->current->setQuery('SELECT * FROM users');
        $profiler->current->finish();
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLog2()
    {
        $profiler = new Profiler\Profiler();
        $handler = new Handler\QueryHandler($profiler, 'query',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), [
                'level' => Log\Logger::WARNING,
                'limit' => 0.001,
                'context' => 'json'

            ]
        );
        $profiler->addStep();
        $profiler->current->setQuery('SELECT * FROM users');
        $profiler->current->finish();
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLogException()
    {
        $this->expectException('Pop\Debug\Handler\Exception');

        $profiler = new Profiler\Profiler();
        $handler  = new Handler\QueryHandler($profiler, 'query',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['foo' => 'test']
        );
        $profiler->addStep();
        $profiler->current->setQuery('SELECT * FROM users');
        $profiler->current->finish();
        $handler->log();
    }

}
