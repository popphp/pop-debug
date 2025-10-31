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
namespace Pop\Debug\Storage;

/**
 * Debug storage interface
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
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
    public function isText(): bool;

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
     * Get debug data by ID
     *
     * @param  string $id
     * @return mixed
     */
    public function getById(string $id): mixed;

    /**
     * Get debug data by type
     *
     * @param  string $type
     * @return mixed
     */
    public function getByType(string $type): mixed;

    /**
     * Determine if debug data exists by ID
     *
     * @param  string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Delete debug data by ID
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
