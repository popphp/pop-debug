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
class Debug
{

    /**
     * Debug handlers
     * @var array
     */
    protected $handlers = [];


    /**
     * Debug storage
     * @var Storage\StorageInterface
     */
    protected $storage = null;

    /**
     * Constructor
     *
     * Instantiate a debug object
     */
    public function __construct()
    {

    }

    /**
     * Add a handler
     *
     * @param  Handler\HandlerInterface
     * @return Debug
     */
    public function addHandler(Handler\HandlerInterface $handler)
    {
        $type = strtolower(str_replace('Handler', '', get_class($handler)));
        if (strrpos($type, '\\') !== false) {
            $type = substr($type, (strrpos($type, '\\') + 1));
        }

        $this->handlers[$type] = $handler;

        return $this;
    }

    /**
     * Determine if the debug object has a handler
     *
     * @param  string $name
     * @return boolean
     */
    public function hasHandler($name)
    {
        return isset($this->handlers[$name]);
    }

    /**
     * Get a handler
     *
     * @param  string $name
     * @return mixed
     */
    public function getHandler($name)
    {
        return (isset($this->handlers[$name])) ? $this->handlers[$name] : null;
    }

    /**
     * Get all handlers
     *
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Set the storage object
     *
     * @param Storage\StorageInterface $storage
     * @return Debug
     */
    public function setStorage(Storage\StorageInterface $storage = null)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Determine if the debug object has storage
     *
     * @return boolean
     */
    public function hasStorage()
    {
        return (null !== $this->storage);
    }

    /**
     * Get the storage object
     *
     * @return Storage\StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

}
