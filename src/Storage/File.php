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
 * Debug file storage class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.2.0
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
     * @param  ?string $format
     */
    public function __construct(string $dir, ?string $format = null)
    {
        parent::__construct($format);
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
     * @param  string $id
     * @param  mixed  $value
     * @return void
     */
    public function save(string $id, mixed $value): void
    {
        $filename = $id;

        if ($this->format == self::JSON) {
            $filename .= '.json';
        } else if ($this->format == self::PHP) {
            $filename .= '.php';
        } else {
            $filename .= '.log';
        }

        file_put_contents($this->dir . DIRECTORY_SEPARATOR . $filename, $this->encodeValue($value));
    }

    /**
     * Get debug data by ID
     *
     * @param  string $id
     * @return mixed
     */
    public function getById(string $id): mixed
    {
        if (str_ends_with($id, '*') || str_ends_with($id, '%')) {
            $id = substr($id, 0, -1);
            return array_values(array_filter(scandir($this->dir), function($value) use ($id) {
                return (($value != '.') && ($value != '..')) && str_starts_with($value, $id);
            }));
        } else {
            return $this->decodeValue($this->dir . DIRECTORY_SEPARATOR . $id);
        }
    }

    /**
     * Get debug data by type
     *
     * @param  string $type
     * @return mixed
     */
    public function getByType(string $type): mixed
    {
        return array_values(array_filter(scandir($this->dir), function($value) use ($type) {
            return (($value != '.') && ($value != '..')) && str_contains($value, $type);
        }));
    }

    /**
     * Determine if debug data exists by ID
     *
     * @param  string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        $fileId = $this->dir . DIRECTORY_SEPARATOR . $id;

        if ($this->format == self::JSON) {
            if (!str_ends_with($fileId, '.json')) {
                $fileId .= '.json';
            }
        } else if ($this->format == self::PHP) {
            if (!str_ends_with($fileId, '.php')) {
                $fileId .= '.php';
            }
        } else {
            if (!str_ends_with($fileId, '.log')) {
                $fileId .= '.log';
            }
        }

        return (file_exists($fileId));
    }

    /**
     * Delete debug data by ID
     *
     * @param  string $id
     * @return void
     */
    public function delete(string $id): void
    {
        $fileId = $this->dir . DIRECTORY_SEPARATOR . $id;

        if ($this->format == self::JSON) {
            if (!str_ends_with($fileId, '.json')) {
                $fileId .= '.json';
            }
        } else if ($this->format == self::PHP) {
            if (!str_ends_with($fileId, '.php')) {
                $fileId .= '.php';
            }
        } else {
            if (!str_ends_with($fileId, '.log')) {
                $fileId .= '.log';
            }
        }

        if (file_exists($fileId)) {
            unlink($fileId);
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
            $value = "<?php" . PHP_EOL . "return unserialize(base64_decode('" .
                base64_encode(serialize($value)) . "'));" . PHP_EOL;
        } else if (!is_string($value)) {
            throw new Exception('Error: The value must be a string if storing in text format.');
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
        if ($this->format == self::JSON) {
            if (!str_ends_with($value, '.json')) {
                $value .= '.json';
            }
            $value  = (file_exists($value)) ? json_decode(file_get_contents($value), true) : false;
        } else if ($this->format == self::PHP) {
            if (!str_ends_with($value, '.php')) {
                $value .= '.php';
            }
            $value  = (file_exists($value)) ? include $value : false;
        } else {
            if (!str_ends_with($value, '.log')) {
                $value .= '.log';
            }
            $value  = (file_exists($value)) ? file_get_contents($value) : false;
        }

        return $value;
    }

}
