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
     * Save debug data
     *
     * @param  string          $id
     * @param  string          $name
     * @param  AbstractHandler $handler
     * @return void
     */
    abstract public function save(string $id, string $name, AbstractHandler $handler): void;

    /**
     * Clear all debug data
     *
     * @return void
     */
    abstract public function clear(): void;

}
