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
 * Debug file storage class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class File extends AbstractStorage
{

    /**
     * Storage dir
     * @var string
     */
    protected $dir = null;

    /**
     * Storage format
     * @var string
     */
    protected $format = 'text';

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
        $this->setDir($dir);
        $this->setFormat($format);
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
            throw new Exception('Error: That cache directory does not exist.');
        } else if (!is_writable($dir)) {
            throw new Exception('Error: That cache directory is not writable.');
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
     * Set the storage format
     *
     * @param  string $format
     * @return File
     */
    public function setFormat($format)
    {
        if (stripos($format, 'json') !== false) {
            $this->format = 'json';
        } else if (stripos($format, 'php') !== false) {
            $this->format = 'php';
        } else {
            $this->format = 'text';
        }

        return $this;
    }

    /**
     * Get the storage format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
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
        $filename = sha1($id);

        if ($this->format == 'json') {
            $value = json_encode($value, JSON_PRETTY_PRINT);
            $filename .= '.json';
        } else if ($this->format == 'php') {
            $value = "return unserialize('" . serialize($value) . "');'";
            $filename .= '.php';
        } else {
            $filename .= '.log';
        }
        file_put_contents($this->dir . DIRECTORY_SEPARATOR . $filename, $value);
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
        $fileId = $this->dir . DIRECTORY_SEPARATOR . sha1($id);

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
        $fileId = $this->dir . DIRECTORY_SEPARATOR . sha1($id);

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
        $fileId = $this->dir . DIRECTORY_SEPARATOR . sha1($id);

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

}
