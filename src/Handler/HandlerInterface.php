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
namespace Pop\Debug\Handler;

use Pop\Log\Logger;

/**
 * Debug handler interface
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.2.0
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
