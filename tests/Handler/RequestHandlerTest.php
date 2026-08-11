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

        public function testPrepareRedactsSensitiveDataByDefault()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $_COOKIE['session_id']  = 'abc123';
            $_SESSION['user_id']    = 42;

            $request = new Handler\RequestHandler();
            $this->assertTrue($request->isRedactingSensitiveData());

            $request->request()->addHeader('Authorization', 'Bearer secret-token');
            $data = $request->prepare();

            $this->assertEquals('[REDACTED]', $data['headers']['Authorization']);
            $this->assertEquals('[REDACTED]', $data['cookie']['session_id']);
            $this->assertEquals('[REDACTED]', $data['session']['user_id']);

            unset($_COOKIE['session_id'], $_SESSION['user_id']);
        }

        public function testPrepareRedactsNestedPostKeys()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $_POST['username']      = 'nick';
            $_POST['credentials']   = ['password' => 'hunter2'];

            $request = new Handler\RequestHandler();
            $data    = $request->prepare();

            $this->assertEquals('nick', $data['post']['username']);
            $this->assertEquals('[REDACTED]', $data['post']['credentials']['password']);

            unset($_POST['username'], $_POST['credentials']);
        }

        public function testPrepareCanDisableRedaction()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $_COOKIE['session_id']  = 'abc123';

            $request = new Handler\RequestHandler();
            $request->setRedactSensitiveData(false);
            $this->assertFalse($request->isRedactingSensitiveData());

            $request->request()->addHeader('Authorization', 'Bearer secret-token');
            $data = $request->prepare();

            $this->assertEquals('abc123', $data['cookie']['session_id']);

            unset($_COOKIE['session_id']);
        }

        public function testCustomRedactedKeys()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $_POST['nickname']      = 'nick';

            $request = new Handler\RequestHandler();
            $request->setRedactedKeys(['nickname']);
            $this->assertEquals(['nickname'], $request->getRedactedKeys());

            $request->addRedactedKey('email');
            $this->assertEquals(['nickname', 'email'], $request->getRedactedKeys());

            $data = $request->prepare();
            $this->assertEquals('[REDACTED]', $data['post']['nickname']);

            unset($_POST['nickname']);
        }

        public function testLogNoLogger()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $handler = new Handler\RequestHandler();
            $handler->log();

            $this->assertFalse($handler->hasLogger());
        }

        public function testLogAcceptsPsr3Logger()
        {
            $_SERVER['REQUEST_URI'] = '/page';
            $logger  = new class implements \Psr\Log\LoggerInterface {
                use \Psr\Log\LoggerTrait;
                public array $logged = [];
                public function log($level, string|\Stringable $message, array $context = []): void
                {
                    $this->logged[] = [$level, (string)$message, $context];
                }
            };

            $handler = new Handler\RequestHandler(null, 'request', $logger, ['level' => 'info']);
            $handler->log();

            $this->assertCount(1, $logger->logged);
        }

    }
}

