<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.0
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
    public function __construct($dir, $format = 'text')
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

        if ($this->format == 'json') {
            $filename .= '.json';
        } else if ($this->format == 'php') {
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
        $fileId = $this->dir . DIRECTORY_SEPARATOR . $id;

        if ($this->format == 'json') {
            $fileId .= '.json';
            $value   = (file_exists($fileId)) ? json_decode(file_get_contents($fileId), true) : false;
        } else if ($this->format == 'php') {
            $fileId .= '.php';
            $value   = (file_exists($fileId)) ? include $fileId : false;
        } else {
            $fileId .= '.log';
            $value   = (file_exists($fileId)) ? file_get_contents($fileId) : false;
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
        $fileId = $this->dir . DIRECTORY_SEPARATOR . $id;

        if ($this->format == 'json') {
            $fileId .= '.json';
        } else if ($this->format == 'php') {
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

        if ($this->format == 'json') {
            $fileId .= '.json';
        } else if ($this->format == 'php') {
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
        if ($this->format == 'json') {
            $value = json_encode($value, JSON_PRETTY_PRINT);
        } else if ($this->format == 'php') {
            $value = "<?php" . PHP_EOL . "return unserialize(base64_decode('" . base64_encode(serialize($value)) . "'));" . PHP_EOL;
        } else if (!is_string($value)) {
            throw new Exception('Error: The value must be a string if storing as a text file.');
        }

        return $value;
    }

}
