<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class ExceptionHandler extends AbstractHandler
{

    /**
     * Verbose flag
     * @var bool
     */
    protected bool $verbose = false;

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
        $this->setVerbose($verbose);
    }

    /**
     * Set verbose
     *
     * @param  bool $verbose
     * @return ExceptionHandler
     */
    public function setVerbose(bool $verbose = true): ExceptionHandler
    {
        $this->verbose = $verbose;
        return $this;
    }

    /**
     * Is verbose
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        return $this->verbose;
    }


    /**
     * Add exception
     *
     * @param  \Exception $exception
     * @return ExceptionHandler
     */
    public function addException(\Exception $exception): ExceptionHandler
    {
        $this->data[] = ['exception' => $exception, 'timestamp' => (string)microtime(true)];
        return $this;
    }

    /**
     * Determine if the handler has exceptions
     *
     * @return bool
     */
    public function hasExceptions(): bool
    {
        return (count($this->data) > 0);
    }

    /**
     * Get exceptions
     *
     * @return array
     */
    public function getExceptions(): array
    {
        return $this->data;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        if ($this->isVerbose()) {
            $exceptions = [];
            foreach ($this->data as $exception) {
                $exceptions[] = [
                    'class'     => get_class($exception['exception']),
                    'code'      => $exception['exception']->getCode(),
                    'line'      => $exception['exception']->getLine(),
                    'file'      => $exception['exception']->getFile(),
                    'message'   => $exception['exception']->getMessage(),
                    'trace'     => $exception['exception']->getTrace(),
                    'timestamp' => $exception['timestamp'],
                ];
            }
            return $exceptions;
        } else {
            return $this->data;
        }
    }

    /**
     * Prepare handler message
     *
     * @param  ?array $context
     * @return string
     */
    public function prepareMessage(?array $context = null): string
    {
        if ($context === null) {
            $context = $this->prepare();
        }

        return (count($context) > 1) ?
            '(' . count($context) . ') exceptions have been thrown.' :
            '(1) exception has been thrown.';
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
                $context = $this->prepare();
                $this->logger->log($logLevel, $this->prepareMessage($context), $context);
            } else {
                throw new Exception('Error: The log level parameter was not set.');
            }
        }
    }

}
