<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
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
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
abstract class AbstractHandler implements HandlerInterface
{

    /**
     * Name of handler
     * @var ?string
     */
    protected ?string $name = null;

    /**
     * Start time
     * @var ?float
     */
    protected ?float $start = null;

    /**
     * End time
     * @var ?float
     */
    protected ?float $end = null;

    /**
     * Elapsed time
     * @var ?float
     */
    protected ?float $elapsed = null;

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
        $this->setStart();
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
     * Start
     *
     * @return AbstractHandler
     */
    public function start(): AbstractHandler
    {
        return $this->setStart();
    }

    /**
     * Stop
     *
     * @return AbstractHandler
     */
    public function stop(): AbstractHandler
    {
        return $this->setEnd();
    }

    /**
     * Set start
     *
     * @param  ?float $start
     * @return AbstractHandler
     */
    public function setStart(?float $start = null): AbstractHandler
    {
        $this->start = $start ?? microtime(true);
        return $this;
    }

    /**
     * Get start
     *
     * @return ?float
     */
    public function getStart(): ?float
    {
        return $this->start;
    }

    /**
     * Has start
     *
     * @return bool
     */
    public function hasStart(): bool
    {
        return !empty($this->start);
    }

    /**
     * Set end
     *
     * @param  ?float $end
     * @return AbstractHandler
     */
    public function setEnd(?float $end = null): AbstractHandler
    {
        $this->end = $end ?? microtime(true);
        if (!empty($this->start)) {
            $this->setElapsed($this->end - $this->start);
        }
        return $this;
    }

    /**
     * Get end
     *
     * @return ?float
     */
    public function getEnd(): ?float
    {
        return $this->end;
    }

    /**
     * Has end
     *
     * @return bool
     */
    public function hasEnd(): bool
    {
        return !empty($this->end);
    }

    /**
     * Set elapsed
     *
     * @param  float $elapsed
     * @return AbstractHandler
     */
    public function setElapsed(float $elapsed): AbstractHandler
    {
        $this->elapsed = $elapsed;
        return $this;
    }

    /**
     * Get elapsed
     *
     * @return ?float
     */
    public function getElapsed(): ?float
    {
        return $this->elapsed;
    }

    /**
     * Has elapsed
     *
     * @return bool
     */
    public function hasElapsed(): bool
    {
        return !empty($this->elapsed);
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
