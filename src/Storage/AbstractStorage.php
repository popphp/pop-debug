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
 * Debug storage abstract class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
abstract class AbstractStorage implements StorageInterface
{

    /**
     * Save debug data
     *
     * @param  string $id
     * @param  mixed  $value
     * @return void
     */
    abstract public function save($id, $value);

    /**
     * Get debug data
     *
     * @param  string $id
     * @return mixed
     */
    abstract public function get($id);

    /**
     * Determine if debug data exists
     *
     * @param  string $id
     * @return mixed
     */
    abstract public function has($id);

    /**
     * Delete debug data
     *
     * @param  string $id
     * @return void
     */
    abstract public function delete($id);

    /**
     * Clear all debug data
     *
     * @return void
     */
    abstract public function clear();

}
