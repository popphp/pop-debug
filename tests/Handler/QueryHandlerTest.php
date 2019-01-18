<?php

namespace Pop\Debug\Test;

use Pop\Debug\Handler;
use Pop\Db\Adapter\Profiler;
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

    public function testPrepareAsString()
    {
        $profiler = new Profiler\Profiler();
        $handler  = new Handler\QueryHandler($profiler);
        $profiler->addStep();
        $profiler->current->setQuery('SELECT * FROM users');
        $profiler->current->finish();

        $string = $handler->prepareHeaderAsString() . $handler->prepareAsString();

        $this->assertContains('Query Handler', $string);
        $this->assertContains('SELECT * FROM users', $string);
    }

}