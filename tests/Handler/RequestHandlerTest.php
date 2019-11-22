<?php

namespace {
    ob_start();
}


namespace Pop\Debug\Test {

    use Pop\Debug\Handler;
    use PHPUnit\Framework\TestCase;

    class RequestHandlerTest extends TestCase
    {

        public function testConstructor()
        {
            $request = new Handler\RequestHandler();
            $this->assertInstanceOf('Pop\Debug\Handler\RequestHandler', $request);
            $this->assertInstanceOf('Pop\Http\Request', $request->getRequest());
            $this->assertInstanceOf('Pop\Http\Request', $request->request());
            $this->assertTrue($request->hasRequest());
            $this->assertNotEmpty($request->getRequestTimestamp());
        }

        public function testPrepare()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $request = new Handler\RequestHandler();

            $data = $request->prepare();

            $this->assertEquals('/page', $data['uri']);
        }

        public function testPrepareAsString()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $request = new Handler\RequestHandler();
            $request->request()->addHeader('Content-Type', 'text/plain');

            $string = $request->prepareHeaderAsString() . $request->prepareAsString();

            $this->assertContains('Request Handler', $string);
            $this->assertContains('URI', $string);
        }

    }
}

