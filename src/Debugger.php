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
namespace Pop\Debug;

use Pop\Debug\Handler\HandlerInterface;
use Pop\Debug\Storage\StorageInterface;
use ArrayIterator;

/**
 * Debugger class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Debugger implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * Debugger handlers
     * @var array
     */
    protected array $handlers = [];

    /**
     * Debugger storage object
     * @var ?StorageInterface
     */
    protected ?StorageInterface $storage = null;

    /**
     * Debugger request ID
     * @var ?string
     */
    protected ?string $requestId = null;

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
                    if ($a instanceof HandlerInterface) {
                        $this->addHandler($a);
                    } else if ($a instanceof StorageInterface) {
                        $this->setStorage($a);
                    }
                }
            } else if ($arg instanceof HandlerInterface) {
                $this->addHandler($arg);
            } else if ($arg instanceof StorageInterface) {
                $this->setStorage($arg);
            }
        }
    }

    /**
     * Add handlers
     *
     * @param  array $handlers
     * @return Debugger
     */
    public function addHandlers(array $handlers): Debugger
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }

        return $this;
    }

    /**
     * Add a handler
     *
     * @param  HandlerInterface $handler
     * @return Debugger
     */
    public function addHandler(HandlerInterface $handler): Debugger
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
     * @return bool
     */
    public function hasHandler(string $name): bool
    {
        return isset($this->handlers[$name]);
    }

    /**
     * Get a handler
     *
     * @param  string $name
     * @return ?HandlerInterface
     */
    public function getHandler(string $name): ?HandlerInterface
    {
        return $this->handlers[$name] ?? null;
    }

    /**
     * Get all handlers
     *
     * @return array
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Set the storage object
     *
     * @param  StorageInterface $storage
     * @return Debugger
     */
    public function setStorage(StorageInterface $storage): Debugger
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Determine if the debug object has storage
     *
     * @return bool
     */
    public function hasStorage(): bool
    {
        return ($this->storage !== null);
    }

    /**
     * Get the storage object
     *
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * Get all data from handlers
     *
     * @return array
     */
    public function getData(): array
    {
        $data = [];
        foreach ($this->handlers as $name => $handler) {
            $data[$name] = ($this->storage->getFormat() == 'TEXT') ? $handler->prepareAsString() : $handler->prepare();
        }
        return $data;
    }

    /**
     * Get stored request by ID
     *
     * @param  string $id
     * @return mixed
     */
    public function getById(string $id): mixed
    {
        return $this->storage->getById($id);
    }

    /**
     * Get stored request by type
     *
     * @param  string $type
     * @return mixed
     */
    public function getByType(string $type): mixed
    {
        return $this->storage->getByType($type);
    }

    /**
     * Determine if debug data exists by ID
     *
     * @param  string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->storage->has($id);
    }

    /**
     * Delete debug data by ID
     *
     * @param  string $id
     * @return void
     */
    public function delete(string $id): void
    {
        $this->storage->delete($id);
    }

    /**
     * Clear storage
     *
     * @return void
     */
    public function clear(): void
    {
        $this->storage->clear();
    }

    /**
     * Save the debug handlers' data to storage
     *
     * @return string
     */
    public function save(): string
    {
        foreach ($this->handlers as $name => $handler) {
            $data = ($this->storage->getFormat() == 'TEXT') ? $handler->prepareAsString() : $handler->prepare();
            $this->storage->save($this->getRequestId() . '-' . $name, $data);
        }

        return $this->getRequestId();
    }

    /**
     * Render the debug handlers' data to string
     *
     * @return string
     */
    public function render(): string
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
    public function getRequestId(): string
    {
        if ($this->requestId === null) {
            $this->requestId = $this->generateId();
        }

        return $this->requestId;
    }

    /**
     * Render the debug handlers' data to string with headers
     *
     * @return string
     */
    public function renderWithHeaders(): string
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
    public function generateId(): string
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
     * Method to get the count of the handlers
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->handlers);
    }

    /**
     * Method to iterate over the handlers
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->handlers);
    }

    /**
     * Set a handler
     *
     * @param  string $name
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->offsetSet($name, $value);
    }

    /**
     * Get a handler
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->offsetGet($name);
    }

    /**
     * Is handler set
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * Unset a handler
     *
     * @param  string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->offsetUnset($name);
    }

    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!($value instanceof HandlerInterface)) {
            throw new Exception('Error: The value passed must be an instance of HandlerInterface');
        }
        $this->handlers[$offset] = $value;
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->handlers[$offset] ?? null;
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->handlers[$offset]);
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
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
    public function __toString(): string
    {
        return $this->renderWithHeaders();
    }

}
