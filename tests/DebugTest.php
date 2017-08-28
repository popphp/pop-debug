<?php

namespace Pop\Debug\Test;

use Pop\Debug\Debug;

class DebugTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $debug = new Debug();
        $this->assertInstanceOf('Pop\Debug\Debug', $debug);
    }

}