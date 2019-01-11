<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Storage;

/**
 * Debug storage abstract class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.0
 */
abstract class AbstractStorage implements StorageInterface
{

    /**
     * Storage format
     * @var string
     */
    protected $format = 'text';

    /**
     * Constructor
     *
     * Instantiate the storage object
     *
     * @param  string $format
     */
    public function __construct($format = 'text')
    {
        $this->setFormat($format);
    }

    /**
     * Set the storage format
     *
     * @param  string $format
     * @return AbstractStorage
     */
    public function setFormat($format)
    {
        if (stripos($format, 'json') !== false) {
            $this->format = 'json';
        } else if (stripos($format, 'php') !== false) {
            $this->format = 'php';
        } else {
            $this->format = 'text';
        }

        return $this;
    }

    /**
     * Determine if the format is text
     *
     * @return boolean
     */
    public function isText()
    {
        return ($this->format == 'text');
    }

    /**
     * Determine if the format is PHP
     *
     * @return boolean
     */
    public function isPhp()
    {
        return ($this->format == 'php');
    }

    /**
     * Determine if the format is JSON
     *
     * @return boolean
     */
    public function isJson()
    {
        return ($this->format == 'json');
    }

    /**
     * Get the storage format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Save debug data
     *
     * @param  string $id
     * @param  mixed  $value
     * @return void
     */
    abstract public function save($id, $value);

    /**
     * Get debug data
     *
     * @param  string $id
     * @return mixed
     */
    abstract public function get($id);

    /**
     * Determine if debug data exists
     *
     * @param  string $id
     * @return mixed
     */
    abstract public function has($id);

    /**
     * Delete debug data
     *
     * @param  string $id
     * @return void
     */
    abstract public function delete($id);

    /**
     * Clear all debug data
     *
     * @return void
     */
    abstract public function clear();

    /**
     * Encode the value based on the format
     *
     * @param  mixed  $value
     * @throws Exception
     * @return string
     */
    abstract public function encodeValue($value);

}
