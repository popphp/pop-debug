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
     * Save debug data
     *
     * @param  string          $id
     * @param  string          $name
     * @param  AbstractHandler $handler
     * @return void
     */
    public function save(string $id, string $name, AbstractHandler $handler): void;

    /**
     * Get debug data by ID
     *
     * @param  string  $id
     * @param  ?string $name
     * @return mixed
     */
    public function getById(string $id, ?string $name = null): mixed;

    /**
     * Determine if debug data exists by ID
     *
     * @param  string  $id
     * @param  ?string $name
     * @return bool
     */
    public function has(string $id, ?string $name = null): bool;

    /**
     * Delete debug data by ID
     *
     * @param  string  $id
     * @param  ?string $name
     * @return void
     */
    public function delete(string $id, ?string $name = null): void;

    /**
     * Clear all debug data
     *
     * @return void
     */
    public function clear(): void;

}
