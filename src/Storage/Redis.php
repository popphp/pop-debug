<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Storage;

/**
 * Debug redis storage class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class Redis extends AbstractStorage
{

    /**
     * Redis object
     * @var \Redis
     */
    protected $redis = null;

    /**
     * Constructor
     *
     * Instantiate the Redis storage object
     *
     * @param  string $host
     * @param  int    $port
     * @throws Exception
     */
    public function __construct($host = 'localhost', $port = 6379)
    {
        if (!class_exists('Redis', false)) {
            throw new Exception('Error: Redis is not available.');
        }

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
    public function redis()
    {
        return $this->redis;
    }

    /**
     * Get the current version of redis.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->redis->info()['redis_version'];
    }

    /**
     * Save debug data
     *
     * @param  string $id
     * @param  mixed  $value
     * @return Redis
     */
    public function save($id, $value)
    {
        $this->redis->set($id, serialize($value));
        return $this;
    }

    /**
     * Get debug data
     *
     * @param  string $id
     * @return mixed
     */
    public function get($id)
    {
        $value = $this->redis->get($id);
        if ($value !== false) {
            $value = unserialize($value);
        }
        return $value;
    }

    /**
     * Determine if debug data exists
     *
     * @param  string $id
     * @return boolean
     */
    public function has($id)
    {
        return ($this->redis->get($id) !== false);
    }

    /**
     * Delete debug data
     *
     * @param  string $id
     * @return Redis
     */
    public function delete($id)
    {
        $this->redis->delete($id);
        return $this;
    }

    /**
     * Clear all debug data
     *
     * @return Redis
     */
    public function clear()
    {
        $this->redis->flushDb();
        return $this;
    }

}
