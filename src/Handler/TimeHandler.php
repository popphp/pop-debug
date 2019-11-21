<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
class TimeHandler extends AbstractHandler
{

    /**
     * Start time
     * @var float
     */
    protected $start = null;

    /**
     * Stop time
     * @var float
     */
    protected $stop = null;

    /**
     * Constructor
     *
     * Instantiate a time handler object
     *
     * @param string  $name
     * @param boolean $start
     */
    public function __construct($name = null, $start = true)
    {
        parent::__construct($name);
        if ($start) {
            $this->start();
        }
    }

    /**
     * Get start value
     *
     * @return float
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Determined if the timer has started
     *
     * @return boolean
     */
    public function hasStarted()
    {
        return (null !== $this->start);
    }

    /**
     * Get stop value
     *
     * @return float
     */
    public function getStop()
    {
        return $this->stop;
    }

    /**
     * Determined if the timer has stopped
     *
     * @return boolean
     */
    public function hasStopped()
    {
        return (null !== $this->stop);
    }

    /**
     * Start timer
     *
     * @return self
     */
    public function start()
    {
        $this->start = microtime(true);
        return $this;
    }

    /**
     * Stop timer
     *
     * @return self
     */
    public function stop()
    {
        $this->stop = microtime(true);
        return $this;
    }

    /**
     * Get elapsed time
     *
     * @return string
     */
    public function getElapsed()
    {
        if (null === $this->stop) {
            $this->stop();
        }
        return number_format(($this->stop - $this->start), 5, '.', '');
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare()
    {
        if (null === $this->stop) {
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
    public function prepareHeaderAsString()
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
    public function prepareAsString()
    {
        if (null === $this->stop) {
            $this->stop();
        }

        $string  = "Start:\t\t\t" . number_format($this->start, 5, '.', '') . PHP_EOL;
        $string .= "Stop:\t\t\t" . number_format($this->stop, 5, '.', '') . PHP_EOL;
        $string .= "Elapsed:\t\t" . $this->getElapsed() . ' seconds' . PHP_EOL . PHP_EOL;

        return $string;
    }

}
