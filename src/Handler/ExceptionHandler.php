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
 * Debug exception handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
class ExceptionHandler extends AbstractHandler
{

    /**
     * Verbose flag
     * @var boolean
     */
    protected $verbose = false;

    /**
     * Exceptions
     * @var array
     */
    protected $exceptions = [];

    /**
     * Constructor
     *
     * Instantiate a handler object
     *
     * @param boolean $verbose
     * @param string  $name
     */
    public function __construct($verbose = false, $name = null)
    {
        parent::__construct($name);
        $this->verbose = (bool)$verbose;
    }

    /**
     * Add exception
     *
     * @param  \Exception $exception
     * @return self
     */
    public function addException(\Exception $exception)
    {
        $this->exceptions[microtime(true)] = $exception;
        return $this;
    }

    /**
     * Determine if the handler has exceptions
     *
     * @return boolean
     */
    public function hasExceptions()
    {
        return (count($this->exceptions) > 0);
    }

    /**
     * Get exceptions
     *
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare()
    {
        $data = [];

        foreach ($this->exceptions as $time => $exception) {
            $data[number_format($time, 5, '.', '')] = $exception;
        }

        return $data;
    }

    /**
     * Prepare header string
     *
     * @return string
     */
    public function prepareHeaderAsString()
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
    public function prepareAsString()
    {
        $string = '';

        foreach ($this->exceptions as $time => $exception) {
            if ($this->verbose) {
                $string .= number_format($time, 5, '.', '') .
                    "\tCode: " . $exception->getCode() .
                    "\tLine: " . $exception->getLine() .
                    "\t" . $exception->getFile() .
                    "\t" . $exception->getMessage() .
                    "\t" . $exception->getTraceAsString() . PHP_EOL . PHP_EOL;
            } else {
                $string .= number_format($time, 5, '.', '') . "\t" . $exception->getMessage() . PHP_EOL;
            }

        }
        $string .= PHP_EOL;

        return $string;
    }

}
