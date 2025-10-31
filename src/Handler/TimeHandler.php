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
 * Debug time handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class TimeHandler extends AbstractHandler
{

    /**
     * Start time
     * @var ?float
     */
    protected ?float $start = null;

    /**
     * Stop time
     * @var ?float
     */
    protected ?float $stop = null;

    /**
     * Constructor
     *
     * Instantiate a time handler object
     *
     * @param  bool    $start
     * @param  ?string $name
     * @param  ?Logger $logger
     * @param  array   $loggingParams
     */
    public function __construct(bool $start = true, ?string $name = null, ?Logger $logger = null, array $loggingParams = [])
    {
        parent::__construct($name, $logger, $loggingParams);
        if ($start) {
            $this->start();
        }
    }

    /**
     * Get start value
     *
     * @return ?float
     */
    public function getStart(): ?float
    {
        return $this->start;
    }

    /**
     * Determined if the timer has started
     *
     * @return bool
     */
    public function hasStarted(): bool
    {
        return ($this->start !== null);
    }

    /**
     * Get stop value
     *
     * @return float
     */
    public function getStop(): float
    {
        return $this->stop;
    }

    /**
     * Determined if the timer has stopped
     *
     * @return bool
     */
    public function hasStopped(): bool
    {
        return ($this->stop !== null);
    }

    /**
     * Start timer
     *
     * @return TimeHandler
     */
    public function start(): TimeHandler
    {
        $this->start = microtime(true);
        return $this;
    }

    /**
     * Stop timer
     *
     * @return TimeHandler
     */
    public function stop(): TimeHandler
    {
        $this->stop = microtime(true);
        return $this;
    }

    /**
     * Get elapsed time
     *
     * @return string
     */
    public function getElapsed(): string
    {
        if ($this->stop === null) {
            $this->stop();
        }
        return number_format(($this->stop - $this->start), 5, '.', '');
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        if ($this->stop === null) {
            $this->stop();
        }

        return [
            'start'   => number_format($this->start, 5, '.', ''),
            'stop'    => number_format($this->stop, 5, '.', ''),
            'elapsed' => $this->getElapsed()
        ];
    }

    /**
     * Prepare header string
     *
     * @return string
     */
    public function prepareHeaderAsString(): string
    {
        $string  = ((!empty($this->name)) ? $this->name . ' ' : '') . 'Time Handler';
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
        if ($this->stop === null) {
            $this->stop();
        }

        $string  = "Start:\t\t\t" . number_format($this->start, 5, '.', '') . PHP_EOL;
        $string .= "Stop:\t\t\t" . number_format($this->stop, 5, '.', '') . PHP_EOL;
        $string .= "Elapsed:\t\t" . $this->getElapsed() . ' seconds' . PHP_EOL . PHP_EOL;

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
            $logLevel   = $this->loggingParams['level'] ?? null;
            $useContext = $this->loggingParams['context'] ?? null;
            $timeLimit  = $this->loggingParams['limit'] ?? null;

            if ($logLevel !== null) {
                $elapsedTime = $this->getElapsed();
                $context     = [];
                if ($timeLimit !== null) {
                    if ($elapsedTime >= $timeLimit) {
                        if (!empty($useContext)) {
                            $context['start']        = $this->start;
                            $context['stop']         = $this->stop;
                            $context['time_limit']   = $timeLimit;
                            $context['elapsed_time'] = $elapsedTime;
                        }
                        if (is_string($useContext)) {
                            $context['format'] = $useContext;
                        }
                        $this->logger->log(
                            $logLevel, 'The time limit of '. $timeLimit . ' second(s) has been exceeded by ' .
                            $elapsedTime - $timeLimit . ' second(s). The timed event was a total of ' .
                            $elapsedTime . ' second(s).', $context
                        );
                    }
                } else {
                    if (!empty($useContext)) {
                        $context['start']        = $this->start;
                        $context['stop']         = $this->stop;
                        $context['elapsed_time'] = $elapsedTime;
                    }
                    if (is_string($useContext)) {
                        $context['format'] = $useContext;
                    }

                    $this->logger->log($logLevel, 'A new ' . $elapsedTime . ' second event has been triggered.', $context);
                }
            } else {
                throw new Exception('Error: The log level parameter was not set.');
            }
        }
    }

}
