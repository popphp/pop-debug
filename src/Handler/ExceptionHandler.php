<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Handler;

use Pop\Log\Logger;

/**
 * Debug exception handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class ExceptionHandler extends AbstractHandler
{

    /**
     * Verbose flag
     * @var bool
     */
    protected bool $verbose = false;

    /**
     * Exceptions
     * @var array
     */
    protected array $exceptions = [];

    /**
     * Constructor
     *
     * Instantiate a handler object
     *
     * @param bool    $verbose
     * @param ?string $name
     * @param ?Logger $logger
     * @param array   $loggingParams
     */
    public function __construct(bool $verbose = false, ?string $name = null, ?Logger $logger = null, array $loggingParams = [])
    {
        parent::__construct($name, $logger, $loggingParams);
        $this->verbose = $verbose;
    }

    /**
     * Add exception
     *
     * @param  \Exception $exception
     * @return ExceptionHandler
     */
    public function addException(\Exception $exception): ExceptionHandler
    {
        $this->exceptions[] = ['exception' => $exception, 'timestamp' => (string)microtime(true)];
        return $this;
    }

    /**
     * Determine if the handler has exceptions
     *
     * @return bool
     */
    public function hasExceptions(): bool
    {
        return (count($this->exceptions) > 0);
    }

    /**
     * Get exceptions
     *
     * @return array
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        return $this->exceptions;
    }

    /**
     * Prepare header string
     *
     * @return string
     */
    public function prepareHeaderAsString(): string
    {
        $string  = ((!empty($this->name)) ? $this->name . ' ' : '') . 'Exception Handler';
        $string .= PHP_EOL . str_repeat('=', strlen($string)) . PHP_EOL;

        return $string;
    }

    /**
     * Prepare handler data as string
     *
     * @return string
     */
    public function prepareAsString(): string
    {
        $string = '';

        foreach ($this->exceptions as $exception) {
            if ($this->verbose) {
                $string .= number_format($exception['timestamp'], 5, '.', '') . PHP_EOL .
                    "Class: " . get_class($exception['exception']) . PHP_EOL .
                    "Code: " . $exception['exception']->getCode() . PHP_EOL .
                    "Line: " . $exception['exception']->getLine() . PHP_EOL .
                    "File: " . $exception['exception']->getFile() . PHP_EOL .
                    "Message: " . $exception['exception']->getMessage() . PHP_EOL .
                    "Trace: " . $exception['exception']->getTraceAsString() . PHP_EOL;
            } else {
                $string .= number_format($exception['timestamp'], 5, '.', '') . "\t" . get_class($exception['exception']) .
                    "\t" . $exception['exception']->getMessage() . PHP_EOL;
            }

        }
        $string .= PHP_EOL;

        return $string;
    }

    /**
     * Trigger handler logging
     *
     * @throws Exception
     * @return void
     */
    public function log(): void
    {
        if (($this->hasLogger()) && ($this->hasLoggingParams())) {
            $logLevel = $this->loggingParams['level'] ?? null;

            if ($logLevel !== null) {
                $exceptionClasses = [];
                foreach ($this->exceptions as $exception) {
                    $exceptionClasses[] = get_class($exception['exception']);
                }

                $message = (count($exceptionClasses) > 1) ?
                    'The following (' . count($exceptionClasses) . ') exceptions have been thrown: ' . implode(', ', $exceptionClasses) :
                    'The following (1) exception has been thrown: ' . implode(', ', $exceptionClasses);

                $this->logger->log($logLevel, $message);
            } else {
                throw new Exception('Error: The log level parameter was not set.');
            }
        }
    }

}
