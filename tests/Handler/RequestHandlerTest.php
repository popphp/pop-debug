<?php

namespace {
    ob_start();
}

namespace Pop\Debug\Test\Handler {

    use Pop\Debug\Handler;
    use Pop\Log;
    use PHPUnit\Framework\TestCase;

    class RequestHandlerTest extends TestCase
    {

        public function testConstructor()
        {
            $request = new Handler\RequestHandler();
            $this->assertInstanceOf('Pop\Debug\Handler\RequestHandler', $request);
            $this->assertInstanceOf('Pop\Http\Server\Request', $request->getRequest());
            $this->assertInstanceOf('Pop\Http\Server\Request', $request->request());
            $this->assertTrue($request->hasRequest());
            $this->assertNotEmpty($request->getStart());
        }

        public function testPrepare()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $request = new Handler\RequestHandler();

            $data = $request->prepare();

            $this->assertEquals('/page', $data['uri']);
        }

        public function testLog1()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $handler = new Handler\RequestHandler(null, 'request',
                new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::INFO, 'context' => 'json']
            );
            $handler->request()->addHeader('Content-Type', 'text/plain');
            $handler->log();

            $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
        }

        public function testLog2()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $handler = new Handler\RequestHandler(null, 'request',
                new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::INFO, 'limit' => 1, 'context' => 'json']
            );
            $handler->request()->addHeader('Content-Type', 'text/plain');
            sleep(2);
            $handler->log();

            $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
        }

        public function testLogException()
        {
            $this->expectException('Pop\Debug\Handler\Exception');
            $_SERVER['REQUEST_URI'] = '/page';
            $handler = new Handler\RequestHandler(null, 'request',
                new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['foo' => 'test']
            );
            $handler->request()->addHeader('Content-Type', 'text/plain');

            $handler->log();
        }

    }
}

