<?php

namespace Pop\Debug\Test;

use Pop\Debug\Handler;
use Pop\Db\Adapter\Profiler;

class QueryHandlerTest extends \PHPUnit_Framework_TestCase
{


    public function testConstructor()
    {
        $profiler = new Profiler\Profiler();
        $handler  = new Handler\QueryHandler($profiler);
        $this->assertInstanceOf('Pop\Debug\Handler\QueryHandler', $handler);
        $profiler->addStep();
        $profiler->current->setQuery('SELECT * FROM users');
        $profiler->current->finish();
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