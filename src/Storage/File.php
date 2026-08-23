<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Storage;

use Pop\Csv\Csv;
use Pop\Debug\Handler\AbstractHandler;

/**
 * Debug file storage class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class File extends AbstractStorage
{

    /**
     * Storage dir
     * @var ?string
     */
    protected ?string $dir = null;

    /**
     * Supported formats
     * @var array
     */
    protected const array FORMATS = ['csv', 'tsv', 'ndjson'];

    /**
     * Format (csv, tsv or ndjson)
     * @var string
     */
    protected string $format = 'csv';

    /**
     * Constructor
     *
     * Instantiate the file storage object
     *
     * @param string $dir
     * @param string $format
     */
    public function __construct(string $dir, string $format = 'csv')
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
     * Has storage dir
     *
     * @return bool
     */
    public function hasDir(): bool
    {
        return (!empty($this->dir) && file_exists($this->dir));
    }

    /**
     * Set the format
     *
     * @param  string $format
     * @throws \InvalidArgumentException
     * @return File
     */
    public function setFormat(string $format): File
    {
        $format = strtolower($format);

        if (!in_array($format, self::FORMATS)) {
            throw new \InvalidArgumentException('Error: The format must be "csv", "tsv" or "ndjson".');
        }

        $this->format = $format;

        return $this;
    }

    /**
     * Get the format
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
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

        $events   = $this->prepareEvents($id, $name, $handler);
        $filename = $this->dir . DIRECTORY_SEPARATOR . $id . '-' . $name . '.' . $this->format;

        if ($this->format === 'ndjson') {
            $this->appendNdJsonToFile($filename, $events);
            return;
        }

        if (!file_exists($filename) && isset($events[0])) {
            file_put_contents($filename, Csv::getFieldHeaders($events[0], (($this->format == 'tsv') ? "\t" : ',')));
        }

        Csv::appendDataToFile($filename, $events, ['delimiter' => (($this->format == 'tsv') ? "\t" : ',')]);
    }

    /**
     * Append events to an NDJSON (JSON Lines) file, one self-contained JSON object per line
     *
     * The 'context' field of each event is already a json_encode()'d string (see
     * AbstractStorage::prepareEvents(), shared with the CSV/TSV formats, where it needs to be a
     * flat scalar cell). For NDJSON it's decoded back into a real nested value first, so it comes
     * out as proper nested JSON instead of a doubly-escaped string.
     *
     * @param  string $filename
     * @param  array  $events
     * @return void
     */
    protected function appendNdJsonToFile(string $filename, array $events): void
    {
        $lines = '';

        foreach ($events as $event) {
            if (isset($event['context']) && is_string($event['context'])) {
                $context = json_decode($event['context'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $event['context'] = $context;
                }
            }

            $encoded = json_encode($event, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            $lines  .= (($encoded !== false) ? $encoded : '{}') . PHP_EOL;
        }

        file_put_contents($filename, $lines, FILE_APPEND);
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
