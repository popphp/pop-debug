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
namespace Pop\Debug;

/**
 * Debug class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class Debug implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * Debug dumps
     * @var array
     */
    protected $dumps = [];

    /**
     * Timestamp format
     * @var string
     */
    protected $timestampFormat = 'Y-m-d H:i:s';

    /**
     * Constructor
     *
     * @param string $timestampFormat
     * Instantiate a debug object
     */
    public function __construct($timestampFormat = null)
    {
        $this->dumps[time()] = 'Pop PHP Debug Start';
        if (null !== $timestampFormat) {
            $this->timestampFormat = $timestampFormat;
        }
    }

    /**
     * Set timestamp format
     *
     * @param  string $timestampFormat
     * @return Debug
     */
    public function setTimestampFormat($timestampFormat)
    {
        $this->timestampFormat = $timestampFormat;
        return $this;
    }

    /**
     * Get timestamp format
     *
     * @return string
     */
    public function getTimestampFormat()
    {
        return $this->timestampFormat;
    }

    /**
     * Dump a message into the debugger
     *
     * @param  string $message
     * @return Debug
     */
    public function dump($message)
    {
        $this->dumps[time()] = $message;
        return $this;
    }

    /**
     * Dump a variable into the debugger
     *
     * @param  string $name
     * @param  mixed  $value
     * @return Debug
     */
    public function dumpVar($name, $value)
    {
        if (!is_string($value) && !is_numeric($value)) {
            $value = var_export($value, true);
        }
        return $this->dump($name . ' => ' . $value);
    }

    /**
     * Get dumps
     *
     * @return array
     */
    public function getDumps()
    {
        return $this->dumps;
    }

    /**
     * Output dumps
     *
     * @return string
     */
    public function output()
    {
        $dumps = '';

        foreach ($this->dumps as $time => $dump) {
            $dumps .= date($this->timestampFormat, $time) . ":\t" . $dump . PHP_EOL;
        }

        return $dumps;
    }

    /**
     * Method to get the count of dumps
     *
     * @return int
     */
    public function count()
    {
        return count($this->dumps);
    }

    /**
     * Method to iterate over the dumps
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->dumps);
    }


    /**
     * Magic get method to return the value of dumps[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (isset($this->dumps[$name])) ? $this->dumps[$name] : null;
    }

    /**
     * Magic set method to set the property to the value of dumps[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->dumps[$name] = $value;
    }

    /**
     * Return the isset value of dumps[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->dumps[$name]);
    }

    /**
     * Unset dumps[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->dumps[$name]);
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * Magic set method to render dump as string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->output();
    }

}
