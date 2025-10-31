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
 * Debug file storage class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class File extends AbstractStorage
{

    /**
     * Storage dir
     * @var ?string
     */
    protected ?string $dir = null;

    /**
     * Constructor
     *
     * Instantiate the file storage object
     *
     * @param  string  $dir
     */
    public function __construct(string $dir)
    {
        $this->setDir($dir);
    }

    /**
     * Set the current storage dir
     *
     * @param  string $dir
     * @throws Exception
     * @return File
     */
    public function setDir(string $dir): File
    {
        if (!file_exists($dir)) {
            throw new Exception('Error: That directory does not exist.');
        } else if (!is_writable($dir)) {
            throw new Exception('Error: That directory is not writable.');
        }

        $this->dir = realpath($dir);

        return $this;
    }

    /**
     * Get the storage dir
     *
     * @return ?string
     */
    public function getDir(): ?string
    {
        return $this->dir;
    }

    /**
     * Save debug data
     *
     * @param  string          $id
     * @param  string          $name
     * @param  AbstractHandler $handler
     * @return void
     */
    public function save(string $id, string $name, AbstractHandler $handler): void
    {
        file_put_contents($this->dir . DIRECTORY_SEPARATOR . $id . '-' . $name . '.log', json_encode($handler->prepare(), JSON_PRETTY_PRINT));
    }

    /**
     * Get debug data by ID
     *
     * @param  string  $id
     * @param  ?string $name
     * @return mixed
     */
    public function getById(string $id, ?string $name = null): mixed
    {
        if ($name !== null) {
            $id .= '-' . $name;
        }
        return (file_exists($this->dir . DIRECTORY_SEPARATOR . $id . '.log')) ?
             json_decode(file_get_contents($this->dir . DIRECTORY_SEPARATOR . $id . '.log'), true) : null;
    }

    /**
     * Determine if debug data exists by ID
     *
     * @param  string $id
     * @return bool
     */
    public function has(string $id, ?string $name = null): bool
    {
        if ($name !== null) {
            $id .= '-' . $name;
        }
        return (file_exists($this->dir . DIRECTORY_SEPARATOR . $id . '.log'));
    }

    /**
     * Delete debug data by ID
     *
     * @param  string  $id
     * @param  ?string $name
     * @return void
     */
    public function delete(string $id, ?string $name = null): void
    {
        if ($name !== null) {
            $id .= '-' . $name;
        }
        if (file_exists($this->dir . DIRECTORY_SEPARATOR . $id . '.log')) {
            unlink($this->dir . DIRECTORY_SEPARATOR . $id . '.log');
        }
    }

    /**
     * Clear all debug data
     *
     * @return void
     */
    public function clear(): void
    {
        if (!$dh = @opendir($this->dir)) {
            return;
        }

        while (false !== ($obj = readdir($dh))) {
            if (($obj != '.') && ($obj != '..') &&
                !is_dir($this->dir . DIRECTORY_SEPARATOR . $obj) && is_file($this->dir . DIRECTORY_SEPARATOR . $obj)) {
                unlink($this->dir . DIRECTORY_SEPARATOR . $obj);
            }
        }

        closedir($dh);
    }

}
