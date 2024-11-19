<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Handler;

use Pop\Log\Logger;

/**
 * Debug handler abstract class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    2.2.0
 */
abstract class AbstractHandler implements HandlerInterface
{

    /**
     * Name of time measurement
     * @var ?string
     */
    protected ?string $name = null;

    /**
     * Logger object
     * @var ?Logger
     */
    protected ?Logger $logger = null;

    /**
     * Logging params
     * @var array
     */
    protected array $loggingParams = [];

    /**
     * Constructor
     *
     * Instantiate a handler object
     *
     * @param ?string $name
     * @param ?Logger $logger
     * @param array   $loggingParams
     */
    public function __construct(?string $name = null, ?Logger $logger = null, array $loggingParams = [])
    {
        if ($name !== null) {
            $this->setName($name);
        }
        if ($logger !== null) {
            $this->setLogger($logger);
            $this->setLoggingParams($loggingParams);
        }
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return AbstractHandler
     */
    public function setName(string $name): AbstractHandler
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Has name
     *
     * @return bool
     */
    public function hasName(): bool
    {
        return !empty($this->name);
    }

    /**
     * Set logger
     *
     * @param  Logger $logger
     * @return AbstractHandler
     */
    public function setLogger(Logger $logger): AbstractHandler
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get logger
     *
     * @return ?Logger
     */
    public function getLogger(): ?Logger
    {
        return $this->logger;
    }

    /**
     * Has logger
     *
     * @return bool
     */
    public function hasLogger(): bool
    {
        return !empty($this->logger);
    }

    /**
     * Set logger
     *
     * @param  array $loggingParams
     * @return AbstractHandler
     */
    public function setLoggingParams(array $loggingParams): AbstractHandler
    {
        $this->loggingParams = $loggingParams;
        return $this;
    }

    /**
     * Get logging params
     *
     * @return array
     */
    public function getLoggingParams(): array
    {
        return $this->loggingParams;
    }

    /**
     * Has logging parameters
     *
     * @return bool
     */
    public function hasLoggingParams(): bool
    {
        return !empty($this->loggingParams);
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    abstract public function prepare(): array;

    /**
     * Prepare header string
     *
     * @return string
     */
    abstract public function prepareHeaderAsString(): string;

    /**
     * Prepare handler data as string
     *
     * @return string
     */
    abstract public function prepareAsString(): string;

    /**
     * Trigger handle logging
     *
     * @return void
     */
    abstract public function log(): void;

}
