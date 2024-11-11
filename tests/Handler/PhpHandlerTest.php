<?php

namespace Pop\Debug\Test\Handler;

use Pop\Debug\Handler;
use Pop\Log;
use PHPUnit\Framework\TestCase;

class PhpHandlerTest extends TestCase
{

    public function testConstructor()
    {
        $handler = new Handler\PhpHandler();
        $this->assertInstanceOf('Pop\Debug\Handler\PhpHandler', $handler);
    }

    public function testGetters()
    {
        $handler = new Handler\PhpHandler();
        $this->assertGreaterThanOrEqual(8, $handler->getPhpMajorVersion());
        $this->assertGreaterThanOrEqual(0, $handler->getPhpMinorVersion());
        $this->assertGreaterThanOrEqual(0, $handler->getPhpReleaseVersion());
        $this->assertEquals(PHP_VERSION, $handler->getPhpVersion());
        $this->assertEquals(PHP_EXTRA_VERSION, $handler->getPhpExtraVersion());
        $this->assertEquals(ini_get('date.timezone'), $handler->getDateTime());
        $this->assertEquals(ini_get('date.timezone'), $handler->getIniSetting('date.timezone'));
        $this->assertEquals(ini_get('error_reporting'), $handler->getErrorSettings()['error_reporting']);
        $this->assertEquals(ini_get('error_reporting'), $handler->getErrorSetting('error_reporting'));
        $this->assertNotEmpty($handler->getErrorReportingList());
        $this->assertTrue($handler->hasErrorLevel(constant($handler->getErrorReportingList()[0])));
        $this->assertNotEmpty($handler->getLimits());
        $this->assertEquals(ini_get('max_execution_time'), $handler->getLimit('max_execution_time'));
        $this->assertNotEmpty($handler->getExtensions());
        $this->assertTrue($handler->hasExtension($handler->getExtensions()[0]));
        $this->assertIsArray($handler->getDisabledFunctions());
        $this->assertIsArray($handler->getDisabledClasses());
        $this->assertFalse($handler->hasDisabledFunction('foo'));
        $this->assertFalse($handler->hasDisabledClass('Foo'));
    }


    public function testPrepare()
    {
        $handler = new Handler\PhpHandler();

        $data = $handler->prepare();

        $this->assertEquals(PHP_VERSION, $data['php_version']);
    }

    public function testPrepareAsString()
    {
        $handler = new Handler\PhpHandler();

        $string = $handler->prepareHeaderAsString() . $handler->prepareAsString();

        $this->assertStringContainsString('PHP Handler', $string);
        $this->assertStringContainsString('PHP Version', $string);
    }

    public function testLog1()
    {
        $handler = new Handler\PhpHandler('php',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::INFO, 'context' => 'json']
        );
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLog2()
    {
        $handler = new Handler\PhpHandler('php',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::WARNING, 'version' => '8.3.0', 'extensions' => 'abc', 'context' => 'json']
        );
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLog3()
    {
        $handler = new Handler\PhpHandler('php',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['level' => Log\Logger::WARNING, 'version' => '8.3.0', 'extensions' => 'abc,xyz', 'context' => 'json']
        );
        $handler->log();

        $this->assertFileExists(__DIR__ . '/../tmp/debug.log');
    }

    public function testLogException()
    {
        $this->expectException('Pop\Debug\Handler\Exception');

        $handler = new Handler\PhpHandler('php',
            new Log\Logger(new Log\Writer\File(__DIR__ . '/../tmp/debug.log')), ['foo' => 'test']
        );
        $handler->log();
    }

}
