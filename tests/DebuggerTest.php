<?php

namespace Pop\Debug\Test;

use Pop\Debug\Debugger;

class DebuggerTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $debugger = new Debugger();
        $this->assertInstanceOf('Pop\Debug\Debugger', $debugger);
    }

}