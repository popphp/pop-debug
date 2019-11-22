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
namespace Pop\Debug\Storage;

/**
 * Debug storage abstract class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
abstract class AbstractStorage implements StorageInterface
{

    /**
     * Format constants
     */
    const JSON = 'JSON';
    const PHP  = 'PHP';

    /**
     * Storage format (json, php or text)
     * @var string
     */
    protected $format = null;

    /**
     * Constructor
     *
     * Instantiate the storage object
     *
     * @param  string $format
     */
    public function __construct($format = null)
    {
        if (null !== $format) {
            $this->setFormat($format);
        }
    }

    /**
     * Set the storage format
     *
     * @param  string $format
     * @return AbstractStorage
     */
    public function setFormat($format)
    {
        switch (strtoupper($format)) {
            case self::JSON:
                $this->format = self::JSON;
                break;
            case self::PHP:
                $this->format = self::PHP;
        }

        return $this;
    }

    /**
     * Determine if the format is PHP
     *
     * @return boolean
     */
    public function isPhp()
    {
        return ($this->format == self::PHP);
    }

    /**
     * Determine if the format is JSON
     *
     * @return boolean
     */
    public function isJson()
    {
        return ($this->format == self::JSON);
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
     * @return string
     */
    abstract public function encodeValue($value);

    /**
     * Decode the value based on the format
     *
     * @param  mixed  $value
     * @return mixed
     */
    abstract public function decodeValue($value);

}
