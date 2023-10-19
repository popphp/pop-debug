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

/**
 * Debug handler interface
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
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

}
