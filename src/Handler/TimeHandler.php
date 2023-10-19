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

/**
 * Debug time handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
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
     * @param ?string $name
     * @param bool    $start
     */
    public function __construct(?string $name = null, bool $start = true)
    {
        parent::__construct($name);
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

        $data = [
            'start'   => number_format($this->start, 5, '.', ''),
            'stop'    => number_format($this->stop, 5, '.', ''),
            'elapsed' => $this->getElapsed()
        ];

        return $data;
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

}
