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

use RedisException;

/**
 * Debug redis storage class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Redis extends AbstractStorage
{

    /**
     * Redis object
     * @var \Redis|null
     */
    protected \Redis|null $redis = null;

    /**
     * Constructor
     *
     * Instantiate the Redis storage object
     *
     * @param  ?string $format
     * @param  string  $host
     * @param  int     $port
     * @throws Exception|RedisException
     */
    public function __construct(?string $format = null, string $host = 'localhost', int $port = 6379)
    {
        if (!class_exists('Redis', false)) {
            throw new Exception('Error: Redis is not available.');
        }

        parent::__construct($format);

        $this->redis = new \Redis();
        if (!$this->redis->connect($host, (int)$port)) {
            throw new Exception('Error: Unable to connect to the redis server.');
        }
    }

    /**
     * Get the redis object.
     *
     * @return \Redis
     */
    public function redis(): \Redis
    {
        return $this->redis;
    }

    /**
     * Get the current version of redis.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->redis->info()['redis_version'];
    }

    /**
     * Save debug data
     *
     * @param  string $id
     * @param  mixed  $value
     * @return void
     */
    public function save(string $id, mixed $value): void
    {
        $this->redis->set($id, $this->encodeValue($value));
    }

    /**
     * Get debug data
     *
     * @param  string $id
     * @return mixed
     */
    public function get(string $id): mixed
    {
        return $this->decodeValue($this->redis->get($id));
    }

    /**
     * Determine if debug data exists
     *
     * @param  string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return ($this->redis->get($id) !== false);
    }

    /**
     * Delete debug data
     *
     * @param  string $id
     * @return void
     */
    public function delete(string $id): void
    {
        $this->redis->del($id);
    }

    /**
     * Clear all debug data
     *
     * @return void
     */
    public function clear(): void
    {
        $this->redis->flushDb();
    }

    /**
     * Encode the value based on the format
     *
     * @param  mixed  $value
     * @throws Exception
     * @return string
     */
    public function encodeValue(mixed $value): string
    {
        if ($this->format == self::JSON) {
            $value = json_encode($value, JSON_PRETTY_PRINT);
        } else if ($this->format == self::PHP) {
            $value = serialize($value);
        } else if (!is_string($value)) {
            throw new Exception('Error: The value must be a string if storing as a text file.');
        }

        return $value;
    }

    /**
     * Decode the value based on the format
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function decodeValue(mixed $value): mixed
    {
        if ($value !== false) {
            if ($this->format == self::JSON) {
                $value = json_decode($value, true);
            } else if ($this->format == self::PHP) {
                $value = unserialize($value);
            }
        }

        return $value;
    }

}
