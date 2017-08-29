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
namespace Pop\Debug\Handler;

/**
 * Debug memory handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class MemoryHandler extends AbstractHandler
{

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
     */
    public function __construct()
    {

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
     * Format memory amount into readable string
     *
     * @param  int $memory
     * @return string
     */
    public function formatMemory($memory)
    {
        if ($memory >= 1000000) {
            $memory = round(($memory / 1000000), 2) . ' MB';
        } else if (($memory < 1000000) && ($memory >= 1000)) {
            $memory = round(($memory / 1000), 2) . ' KB';
        } else if ($memory < 1000) {
            $memory = $memory . ' B';
        }

        return $memory;
    }

}
