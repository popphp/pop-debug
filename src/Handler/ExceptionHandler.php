<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Handler;

use Psr\Log\LoggerInterface;

/**
 * Debug exception handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.0
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
     * @param bool             $verbose
     * @param ?string          $name
     * @param ?LoggerInterface $logger
     * @param array            $loggingParams
     */
    public function __construct(bool $verbose = false, ?string $name = null, ?LoggerInterface $logger = null, array $loggingParams = [])
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
                    'exception' => get_class($exception['exception']),
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
        $logLevel = $this->resolveLogLevel();
        if ($logLevel === null) {
            return;
        }

        $context = $this->prepare();
        $this->logger->log($logLevel, $this->prepareMessage($context), $context);
    }

}
