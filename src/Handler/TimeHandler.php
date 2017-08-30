<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class TimeHandler extends AbstractHandler
{

    /**
     * Name of time measurement
     * @var string
     */
    protected $name = null;

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
    public function __construct($name, $start = false)
    {
        $this->name = $name;
        if ($start) {
            $this->start();
        }
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
        return number_format(($this->stop - $this->start), 5);
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

        $data = [];
        return $data;
    }

}
