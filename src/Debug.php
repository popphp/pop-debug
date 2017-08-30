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

use Pop\Debug\Handler;
use Pop\Debug\Storage;

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
class Debug implements \ArrayAccess
{

    /**
     * Debug handlers
     * @var array
     */
    protected $handlers = [];

    /**
     * Debug storage object
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
        $args = func_get_args();

        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $a) {
                    if ($a instanceof Handler\HandlerInterface) {
                        $this->addHandler($a);
                    } else if ($a instanceof Storage\StorageInterface) {
                        $this->setStorage($a);
                    }
                }
            } else if ($arg instanceof Handler\HandlerInterface) {
                $this->addHandler($arg);
            } else if ($arg instanceof Storage\StorageInterface) {
                $this->setStorage($arg);
            }
        }
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
            if (!empty($handler->getName())) {
                $type .= '-' . str_replace(' ', '-', strtolower($handler->getName()));
            }
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
    public function setStorage(Storage\StorageInterface $storage)
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

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->handlers[$offset]);
    }
    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return (isset($this->handlers[$offset])) ? $this->handlers[$offset] : null;
    }
    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof Handler\HandlerInterface)) {
            throw new Exception('Error: The value passed must be an instance of HandlerInterface');
        }
        $this->handlers[$offset] = $value;
    }
    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (isset($this->handlers[$offset])) {
            unset($this->handlers[$offset]);
        }
    }

}
