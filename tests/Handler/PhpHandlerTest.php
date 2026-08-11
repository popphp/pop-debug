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

    public function testLogNoLogger()
    {
        $handler = new Handler\PhpHandler();
        $handler->log();

        $this->assertFalse($handler->hasLogger());
    }

    public function testLogVersionExceeded()
    {
        $logFile = __DIR__ . '/../tmp/debug.log';
        $handler = new Handler\PhpHandler('php',
            new Log\Logger(new Log\Writer\File($logFile)),
            ['level' => Log\Logger::WARNING, 'version' => '99.0.0', 'context' => 'json']
        );
        $handler->log();

        $this->assertStringContainsString('is less than the required version', file_get_contents($logFile));
    }

    public function testParseErrorSettingsWithErrorReportingAll()
    {
        $original = ini_set('error_reporting', (string)E_ALL);

        try {
            $handler = new Handler\PhpHandler();
            $this->assertEquals(['E_ALL'], $handler->getErrorReportingList());
        } finally {
            ini_set('error_reporting', $original);
        }
    }

    /**
     * disable_functions/disable_classes are PHP_INI_SYSTEM settings - they can only be set at
     * process startup (php.ini or a -d flag), not at runtime via ini_set(), so parseDisabled()'s
     * non-empty branches can't be exercised in-process. A subprocess started with both set is
     * the only way to reach them.
     */
    public function testParseDisabledInSubprocess()
    {
        $autoload = __DIR__ . '/../../vendor/autoload.php';
        $script   = 'require ' . var_export($autoload, true) . ';'
            . '$h = new Pop\Debug\Handler\PhpHandler();'
            . 'echo json_encode(["functions" => $h->getDisabledFunctions(), "classes" => $h->getDisabledClasses()]);';

        $command = sprintf(
            'php -d disable_functions=%s -d disable_classes=%s -r %s',
            escapeshellarg('chgrp,chown'),
            escapeshellarg('FakeDisabledTestClass'),
            escapeshellarg($script)
        );

        $output = shell_exec($command);
        $result = json_decode((string)$output, true);

        $this->assertIsArray($result);
        $this->assertEquals(['chgrp', 'chown'], $result['functions']);
        $this->assertEquals(['FakeDisabledTestClass'], $result['classes']);
    }

}
