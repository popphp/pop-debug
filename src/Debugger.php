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
namespace Pop\Debug;

use Pop\Debug\Handler;
use Pop\Debug\Storage;

/**
 * Debugger class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.0
 */
class Debugger implements \ArrayAccess
{

    /**
     * Debugger handlers
     * @var array
     */
    protected $handlers = [];

    /**
     * Debugger storage object
     * @var Storage\StorageInterface
     */
    protected $storage = null;

    /**
     * Debugger request ID
     * @var string
     */
    protected $requestId = null;

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
     * @return Debugger
     */
    public function addHandler(Handler\HandlerInterface $handler)
    {
        $type = strtolower(str_replace('Handler', '', get_class($handler)));
        if (strrpos($type, '\\') !== false) {
            $type = substr($type, (strrpos($type, '\\') + 1));
            if (!empty($handler->getName())) {
                $type = str_replace(' ', '-', strtolower($handler->getName())) . '-' . $type;
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
     * @return Debugger
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
     * Get all data from handlers
     *
     * @return array
     */
    public function getData()
    {
        $data = [];
        foreach ($this->handlers as $name => $handler) {
            $data[$name] = ($this->storage->getFormat() == 'text') ? $handler->prepareAsString() : $handler->prepare();
        }
        return $data;
    }

    /**
     * Save the debug handlers' data to storage
     *
     * @return void
     */
    public function save()
    {
        foreach ($this->handlers as $name => $handler) {
            $data = ($this->storage->getFormat() == 'text') ? $handler->prepareAsString() : $handler->prepare();
            $this->storage->save($this->getRequestId() . '-' . $name, $data);
        }
    }

    /**
     * Render the debug handlers' data to string
     *
     * @return string
     */
    public function render()
    {
        $output = '';

        foreach ($this->handlers as $handler) {
            $output .= $handler->prepareAsString();
        }

        return $output;
    }

    /**
     * Get current request ID
     *
     * @return string
     */
    public function getRequestId()
    {
        if (null === $this->requestId) {
            $this->requestId = $this->generateId();
        }

        return $this->requestId;
    }

    /**
     * Render the debug handlers' data to string with headers
     *
     * @return string
     */
    public function renderWithHeaders()
    {
        $output = '';

        foreach ($this->handlers as $handler) {
            $output .= $handler->prepareHeaderAsString() . $handler->prepareAsString();
        }

        return $output;
    }

    /**
     * Generate unique ID
     *
     * @return string
     */
    public function generateId()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(16));
        } else if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            return md5(uniqid());
        }
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

    /**
     * Render to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->renderWithHeaders();
    }

}
