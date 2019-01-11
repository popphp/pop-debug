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
namespace Pop\Debug\Handler;

/**
 * Debug memory handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.0
 */
class MemoryHandler extends AbstractHandler
{

    /**
     * Memory limit
     * @var int
     */
    protected $limit = 0;

    /**
     * Memory usage snapshots
     * @var array
     */
    protected $usages = [];

    /**
     * Peak memory usage snapshots
     * @var array
     */
    protected $peaks = [];

    /**
     * Constructor
     *
     * Instantiate a memory handler object
     *
     * @param  string   $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->limit = $this->formatMemoryToInt(ini_get('memory_limit'));
    }

    /**
     * Get memory limit
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Take a memory usage snapshot
     *
     * @param  boolean $real
     * @return self
     */
    public function updateMemoryUsage($real = false)
    {
        $this->usages[microtime(true)] = memory_get_usage($real);
        return $this;
    }

    /**
     * Determine if the handler has memory usages snapshots
     *
     * @return boolean
     */
    public function hasUsages()
    {
        return (count($this->usages) > 0);
    }

    /**
     * Get memory usages snapshots
     *
     * @return array
     */
    public function getUsages()
    {
        return $this->usages;
    }

    /**
     * Take a peak memory usage snapshot
     *
     * @param  boolean $real
     * @return self
     */
    public function updatePeakMemoryUsage($real = false)
    {
        $this->peaks[microtime(true)] = memory_get_peak_usage($real);
        return $this;
    }

    /**
     * Determine if the handler has peak memory usages snapshots
     *
     * @return boolean
     */
    public function hasPeakUsages()
    {
        return (count($this->peaks) > 0);
    }

    /**
     * Get peak memory usages snapshots
     *
     * @return array
     */
    public function getPeakUsages()
    {
        return $this->peaks;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare()
    {
        $data = [
            'limit'  => $this->formatMemoryToString($this->limit),
            'usages' => [],
            'peaks'  => []
        ];

        foreach ($this->usages as $time => $usage) {
            $data['usages'][number_format($time, 5, '.', '')] = $this->formatMemoryToString($usage);
        }

        foreach ($this->peaks as $time => $peak) {
            $data['peaks'][number_format($time, 5, '.', '')] = $this->formatMemoryToString($peak);
        }

        return $data;
    }

    /**
     * Prepare header string
     *
     * @return string
     */
    public function prepareHeaderAsString()
    {
        $string  = ((!empty($this->name)) ? $this->name . ' ' : '') . 'Memory Handler';
        $string .= PHP_EOL . str_repeat('=', strlen($string)) . PHP_EOL;

        return $string;
    }

    /**
     * Prepare handler data as string
     *
     * @return string
     */
    public function prepareAsString()
    {
        $string  = "Limit:\t\t\t" . $this->formatMemoryToString($this->limit) . PHP_EOL . PHP_EOL;

        $string .= "Usages:" . PHP_EOL;
        $string .= "-------" . PHP_EOL;
        foreach ($this->usages as $time => $usage) {
            $string .= number_format($time, 5, '.', '') . "\t" . $this->formatMemoryToString($usage) . PHP_EOL;
        }
        $string .= PHP_EOL;

        $string .= "Peaks:" . PHP_EOL;
        $string .= "------" . PHP_EOL;
        foreach ($this->peaks as $time => $peak) {
            $string .= number_format($time, 5, '.', '') . "\t" . $this->formatMemoryToString($peak) . PHP_EOL;
        }
        $string .= PHP_EOL;

        return $string;
    }

    /**
     * Format memory amount into readable string
     *
     * @param  int $memory
     * @param  int $bytes
     * @return string
     */
    public function formatMemoryToString($memory, $bytes = 1024)
    {
        if ($memory >= pow($bytes, 3)) {
            $memory = round(($memory / pow($bytes, 3)), 2) . 'GB';
        } else if ($memory >= pow($bytes, 2)) {
            $memory = round(($memory / pow($bytes, 2)), 2) . 'MB';
        } else if (($memory < pow($bytes, 2)) && ($memory >= $bytes)) {
            $memory = round(($memory / $bytes), 2) . 'KB';
        } else if ($memory < $bytes) {
            $memory = $memory . 'B';
        }

        return $memory;
    }

    /**
     * Format memory amount into integer
     *
     * @param  int $memory
     * @param  int $bytes
     * @return int
     */
    public function formatMemoryToInt($memory, $bytes = 1024)
    {
        $factor = 1;

        if (stripos($memory, 'G') !== false) {
            $factor = pow($bytes, 3);
        } else if (stripos($memory, 'M') !== false) {
            $factor = pow($bytes, 2);
        } else if (stripos($memory, 'K') !== false) {
            $factor = $bytes;
        }

        return (int)$memory * $factor;
    }

}
