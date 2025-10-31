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

use Pop\Debug\Handler\AbstractHandler;

/**
 * Debug storage abstract class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
abstract class AbstractStorage implements StorageInterface
{

    /**
     * Format constants
     */
    const TEXT = 'TEXT';
    const JSON = 'JSON';
    const PHP  = 'PHP';

    /**
     * Storage format (json, php or text)
     * @var string
     */
    protected string $format = 'TEXT';

    /**
     * Constructor
     *
     * Instantiate the storage object
     *
     * @param  ?string $format
     */
    public function __construct(?string $format = self::TEXT)
    {
        if ($format !== null) {
            $this->setFormat($format);
        }
    }

    /**
     * Set the storage format
     *
     * @param  string $format
     * @return AbstractStorage
     */
    public function setFormat(string $format): AbstractStorage
    {
        $this->format = match (strtoupper($format)) {
            self::JSON => self::JSON,
            self::PHP  => self::PHP,
            default    => self::TEXT,
        };

        return $this;
    }

    /**
     * Determine if the format is PHP
     *
     * @return bool
     */
    public function isText(): bool
    {
        return ($this->format == self::TEXT);
    }

    /**
     * Determine if the format is PHP
     *
     * @return bool
     */
    public function isPhp(): bool
    {
        return ($this->format == self::PHP);
    }

    /**
     * Determine if the format is JSON
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return ($this->format == self::JSON);
    }

    /**
     * Get the storage format
     *
     * @return ?string
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * Save debug data
     *
     * @param  string          $id
     * @param  string          $name
     * @param  AbstractHandler $handler
     * @return void
     */
    abstract public function save(string $id, string $name, AbstractHandler $handler): void;

    /**
     * Get debug data by ID
     *
     * @param  string $id
     * @return mixed
     */
    abstract public function getById(string $id): mixed;

    /**
     * Get debug data by type
     *
     * @param  string $type
     * @return mixed
     */
    abstract public function getByType(string $type): mixed;

    /**
     * Determine if debug data exists by
     *
     * @param  string $id
     * @return bool
     */
    abstract public function has(string $id): bool;

    /**
     * Delete debug data by id
     *
     * @param  string $id
     * @return void
     */
    abstract public function delete(string $id): void;

    /**
     * Clear all debug data
     *
     * @return void
     */
    abstract public function clear(): void;

    /**
     * Encode the value based on the format
     *
     * @param  mixed  $value
     * @return string
     */
    abstract public function encodeValue(mixed $value): string;

    /**
     * Decode the value based on the format
     *
     * @param  mixed  $value
     * @return mixed
     */
    abstract public function decodeValue(mixed $value): mixed;
}
