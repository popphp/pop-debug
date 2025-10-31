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
 * Debug handler interface
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
interface HandlerInterface
{

    /**
     * Set name
     *
     * @param  string  $name
     * @return HandlerInterface
     */
    public function setName(string $name): HandlerInterface;

    /**
     * Get name
     *
     * @return ?string
     */
    public function getName(): ?string;

    /**
     * Has name
     *
     * @return bool
     */
    public function hasName(): bool;

    /**
     * Start
     *
     * @return HandlerInterface
     */
    public function start(): HandlerInterface;

    /**
     * Stop
     *
     * @return HandlerInterface
     */
    public function stop(): HandlerInterface;

    /**
     * Set start
     *
     * @param  ?float $start
     * @return HandlerInterface
     */
    public function setStart(?float $start = null): HandlerInterface;

    /**
     * Get start
     *
     * @return ?float
     */
    public function getStart(): ?float;

    /**
     * Has start
     *
     * @return bool
     */
    public function hasStart(): bool;

    /**
     * Set end
     *
     * @param  ?float $end
     * @return HandlerInterface
     */
    public function setEnd(?float $end = null): HandlerInterface;

    /**
     * Get end
     *
     * @return ?float
     */
    public function getEnd(): ?float;

    /**
     * Has end
     *
     * @return bool
     */
    public function hasEnd(): bool;

    /**
     * Set elapsed
     *
     * @param  float $elapsed
     * @return HandlerInterface
     */
    public function setElapsed(float $elapsed): HandlerInterface;

    /**
     * Get elapsed
     *
     * @return ?float
     */
    public function getElapsed(): ?float;

    /**
     * Has elapsed
     *
     * @return bool
     */
    public function hasElapsed(): bool;

    /**
     * Set logger
     *
     * @param  Logger $logger
     * @return HandlerInterface
     */
    public function setLogger(Logger $logger): HandlerInterface;

    /**
     * Get logger
     *
     * @return ?Logger
     */
    public function getLogger(): ?Logger;

    /**
     * Has logger
     *
     * @return bool
     */
    public function hasLogger(): bool;

    /**
     * Set logger
     *
     * @param  array $loggingParams
     * @return HandlerInterface
     */
    public function setLoggingParams(array $loggingParams): HandlerInterface;

    /**
     * Get logging params
     *
     * @return array
     */
    public function getLoggingParams(): array;

    /**
     * Has logging parameters
     *
     * @return bool
     */
    public function hasLoggingParams(): bool;

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array;

    /**
     * Prepare header string
     *
     * @return string
     */
    public function prepareHeaderAsString(): string;

    /**
     * Prepare handler data as string
     *
     * @return string
     */
    public function prepareAsString(): string;

    /**
     * Trigger handle logging
     *
     * @return void
     */
    public function log(): void;

}
