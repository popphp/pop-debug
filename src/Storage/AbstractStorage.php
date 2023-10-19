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
 * Debug storage abstract class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
abstract class AbstractStorage implements StorageInterface
{

    /**
     * Format constants
     */
    const JSON = 'JSON';
    const PHP  = 'PHP';

    /**
     * Storage format (json, php or text)
     * @var ?string
     */
    protected ?string $format = null;

    /**
     * Constructor
     *
     * Instantiate the storage object
     *
     * @param  ?string $format
     */
    public function __construct(?string $format = null)
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
        switch (strtoupper($format)) {
            case self::JSON:
                $this->format = self::JSON;
                break;
            case self::PHP:
                $this->format = self::PHP;
        }

        return $this;
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
     * @return string
     */
    public function getFormat(): bool
    {
        return $this->format;
    }

    /**
     * Save debug data
     *
     * @param  string $id
     * @param  mixed  $value
     * @return void
     */
    abstract public function save(string $id, mixed $value): void;

    /**
     * Get debug data
     *
     * @param  string $id
     * @return mixed
     */
    abstract public function get(string $id): mixed;

    /**
     * Determine if debug data exists
     *
     * @param  string $id
     * @return bool
     */
    abstract public function has(string $id): bool;

    /**
     * Delete debug data
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
