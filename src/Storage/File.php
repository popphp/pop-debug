<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
class File extends AbstractStorage
{

    /**
     * Storage dir
     * @var string
     */
    protected $dir = null;

    /**
     * Constructor
     *
     * Instantiate the file storage object
     *
     * @param  string $dir
     * @param  string $format
     */
    public function __construct($dir, $format = null)
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
    public function setDir($dir)
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
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Save debug data
     *
     * @param  string $id
     * @param  mixed  $value
     * @return File
     */
    public function save($id, $value)
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
        return $this->decodeValue($this->dir . DIRECTORY_SEPARATOR . $id);
    }

    /**
     * Determine if debug data exists
     *
     * @param  string $id
     * @return boolean
     */
    public function has($id)
    {
        $fileId = $this->dir . DIRECTORY_SEPARATOR . $id;

        if ($this->format == self::JSON) {
            $fileId .= '.json';
        } else if ($this->format == self::PHP) {
            $fileId .= '.php';
        } else {
            $fileId .= '.log';
        }

        return (file_exists($fileId));
    }

    /**
     * Delete debug data
     *
     * @param  string $id
     * @return File
     */
    public function delete($id)
    {
        $fileId = $this->dir . DIRECTORY_SEPARATOR . $id;

        if ($this->format == self::JSON) {
            $fileId .= '.json';
        } else if ($this->format == self::PHP) {
            $fileId .= '.php';
        } else {
            $fileId .= '.log';
        }

        if (file_exists($fileId)) {
            unlink($fileId);
        }

        return $this;
    }

    /**
     * Clear all debug data
     *
     * @return File
     */
    public function clear()
    {
        if (!$dh = @opendir($this->dir)) {
            return $this;
        }

        while (false !== ($obj = readdir($dh))) {
            if (($obj != '.') && ($obj != '..') &&
                !is_dir($this->dir . DIRECTORY_SEPARATOR . $obj) && is_file($this->dir . DIRECTORY_SEPARATOR . $obj)) {
                unlink($this->dir . DIRECTORY_SEPARATOR . $obj);
            }
        }

        closedir($dh);

        return $this;
    }

    /**
     * Encode the value based on the format
     *
     * @param  mixed  $value
     * @throws Exception
     * @return string
     */
    public function encodeValue($value)
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
    public function decodeValue($value)
    {
        if ($this->format == self::JSON) {
            $value .= '.json';
            $value  = (file_exists($value)) ? json_decode(file_get_contents($value), true) : false;
        } else if ($this->format == self::PHP) {
            $value .= '.php';
            $value  = (file_exists($value)) ? include $value : false;
        } else {
            $value .= '.log';
            $value  = (file_exists($value)) ? file_get_contents($value) : false;
        }

        return $value;
    }

}
