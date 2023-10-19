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
namespace Pop\Debug\Storage;

/**
 * Debug storage interface
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
interface StorageInterface
{

    /**
     * Set the storage format
     *
     * @param  string $format
     * @return StorageInterface
     */
    public function setFormat(string $format): StorageInterface;

    /**
     * Determine if the format is PHP
     *
     * @return bool
     */
    public function isPhp(): bool;

    /**
     * Determine if the format is JSON
     *
     * @return bool
     */
    public function isJson(): bool;

    /**
     * Get the storage format
     *
     * @return ?string
     */
    public function getFormat(): ?string;

    /**
     * Save debug data
     *
     * @param  string $id
     * @param  mixed  $value
     * @return void
     */
    public function save(string $id, mixed $value): void;

    /**
     * Get debug data
     *
     * @param  string $id
     * @return mixed
     */
    public function get(string $id): mixed;

    /**
     * Determine if debug data exists
     *
     * @param  string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Delete debug data
     *
     * @param  string $id
     * @return void
     */
    public function delete(string $id): void;

    /**
     * Clear all debug data
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Encode the value based on the format
     *
     * @param  mixed $value
     * @return string
     */
    public function encodeValue(mixed $value): string;

    /**
     * Decode the value based on the format
     *
     * @param  mixed $value
     * @return mixed
     */
    public function decodeValue(mixed $value): mixed;

}
