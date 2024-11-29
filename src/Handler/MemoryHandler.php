<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Handler;

use Pop\Log\Logger;

/**
 * Debug memory handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    2.2.1
 */
class MemoryHandler extends AbstractHandler
{

    /**
     * Actual bytes flag
     * @var bool
     */
    protected bool $actualBytes = false;

    /**
     * Memory limit
     * @var int
     */
    protected int $limit = 0;

    /**
     * Memory usage snapshots
     * @var array
     */
    protected array $usages = [];

    /**
     * Peak memory usage snapshots
     * @var array
     */
    protected array $peaks = [];

    /**
     * Constructor
     *
     * Instantiate a memory handler object
     *
     * @param bool    $actualBytes
     * @param ?string $name
     * @param ?Logger $logger
     * @param array   $loggingParams
     */
    public function __construct(bool $actualBytes = false, ?string $name = null, ?Logger $logger = null, array $loggingParams = [])
    {
        parent::__construct($name, $logger, $loggingParams);
        $this->actualBytes = $actualBytes;
        $this->limit       = $this->formatMemoryToInt(ini_get('memory_limit'));
    }

    /**
     * Get memory limit
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Take both a memory usage and peak usage snapshot
     *
     * @param  bool $real
     * @return MemoryHandler
     */
    public function updateUsage(bool $real = false): MemoryHandler
    {
        $this->updateMemoryUsage($real)
            ->updatePeakMemoryUsage($real);

        return $this;
    }

    /**
     * Take a memory usage snapshot
     *
     * @param  bool $real
     * @return MemoryHandler
     */
    public function updateMemoryUsage(bool $real = false): MemoryHandler
    {
        $this->usages[] = ['memory' => memory_get_usage($real), 'timestamp' => (string)microtime(true)];
        return $this;
    }

    /**
     * Determine if the handler has memory usages snapshots
     *
     * @return bool
     */
    public function hasUsages(): bool
    {
        return (count($this->usages) > 0);
    }

    /**
     * Get memory usages snapshots
     *
     * @return array
     */
    public function getUsages(): array
    {
        return $this->usages;
    }

    /**
     * Take a peak memory usage snapshot
     *
     * @param  bool $real
     * @return MemoryHandler
     */
    public function updatePeakMemoryUsage(bool $real = false): MemoryHandler
    {
        $this->peaks[] = ['memory' => memory_get_peak_usage($real), 'timestamp' => (string)microtime(true)];
        return $this;
    }

    /**
     * Determine if the handler has peak memory usages snapshots
     *
     * @return bool
     */
    public function hasPeakUsages(): bool
    {
        return (count($this->peaks) > 0);
    }

    /**
     * Get peak memory usages snapshots
     *
     * @return array
     */
    public function getPeakUsages(): array
    {
        return $this->peaks;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        $data = [
            'limit'  => (!$this->actualBytes) ? $this->formatMemoryToString($this->limit) : $this->limit,
            'usages' => [],
            'peaks'  => []
        ];

        foreach ($this->usages as $usage) {
            $data['usages'][] = [
                'memory'    => (!$this->actualBytes) ? $this->formatMemoryToString($usage['memory']) : $usage['memory'],
                'timestamp' => number_format($usage['timestamp'], 5, '.', '')
            ];
        }

        foreach ($this->peaks as $peak) {
            $data['peaks'][] = [
                'memory'    => (!$this->actualBytes) ? $this->formatMemoryToString($peak['memory']) : $peak['memory'],
                'timestamp' => number_format($peak['timestamp'], 5, '.', '')
            ];
        }

        return $data;
    }

    /**
     * Prepare header string
     *
     * @return string
     */
    public function prepareHeaderAsString(): string
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
    public function prepareAsString(): string
    {
        $string  = "Limit:\t\t\t" .
            ((!$this->actualBytes) ? $this->formatMemoryToString($this->limit) : $this->limit) . PHP_EOL . PHP_EOL;

        $string .= "Usages:" . PHP_EOL;
        $string .= "-------" . PHP_EOL;
        foreach ($this->usages as $usage) {
            $string .= number_format($usage['timestamp'], 5, '.', '') . "\t" .
                ((!$this->actualBytes) ? $this->formatMemoryToString($usage['memory']) : $usage['memory']) . PHP_EOL;
        }
        $string .= PHP_EOL;

        $string .= "Peaks:" . PHP_EOL;
        $string .= "------" . PHP_EOL;
        foreach ($this->peaks as $peak) {
            $string .= number_format($peak['timestamp'], 5, '.', '') . "\t" .
                ((!$this->actualBytes) ? $this->formatMemoryToString($peak['memory']) : $peak['memory']) . PHP_EOL;
        }
        $string .= PHP_EOL;

        return $string;
    }

    /**
     * Trigger handler logging
     *
     * @throws Exception
     * @return void
     */
    public function log(): void
    {
        if (($this->hasLogger()) && ($this->hasLoggingParams())) {
            $logLevel   = $this->loggingParams['level'] ?? null;
            $useContext = $this->loggingParams['context'] ?? null;
            $usageLimit = $this->loggingParams['usage_limit'] ?? null;
            $peakLimit  = $this->loggingParams['peak_limit'] ?? null;

            if ($logLevel !== null) {
                // Log general usage
                if (($usageLimit === null) && ($peakLimit === null)) {
                    foreach ($this->usages as $usage) {
                        $context = [];
                        if (!empty($useContext)) {
                            $context['memory_limit'] = $this->limit;
                            $context['memory_usage'] = $usage['memory'];
                        }
                        if (is_string($useContext)) {
                            $context['format'] = $useContext;
                        }
                        $this->logger->log($logLevel, 'Memory Usage: ' . $usage['memory'] . ' bytes.', $context);
                    }
                    foreach ($this->peaks as $peak) {
                        $context = [];
                        if (!empty($useContext)) {
                            $context['memory_limit']      = $this->limit;
                            $context['peak_memory_usage'] = $peak['memory'];
                        }
                        if (is_string($useContext)) {
                            $context['format'] = $useContext;
                        }
                        $this->logger->log($logLevel, 'Peak Memory Usage: ' . $peak['memory'] . ' bytes.', $context);
                    }
                // Log if limits are exceeded
                } else {
                    if ($usageLimit !== null)  {
                        foreach ($this->usages as $usage) {
                            if ($usage['memory'] >= $usageLimit) {
                                $context = [];
                                if (!empty($useContext)) {
                                    $context['memory_limit'] = $this->limit;
                                    $context['usage_limit']  = $usageLimit;
                                    $context['memory_usage'] = $usage['memory'];
                                }
                                if (is_string($useContext)) {
                                    $context['format'] = $useContext;
                                }
                                $this->logger->log($logLevel, 'Memory usage limit of ' . $usageLimit . ' has been exceeded by ' .
                                    $usage['memory'] - $usageLimit. ' bytes. ' . $usage['memory'] . ' bytes were used.', $context);
                            }
                        }
                    }
                    if ($peakLimit !== null) {
                        foreach ($this->peaks as $peak) {
                            if ($peak['memory'] >= $peakLimit) {
                                $context = [];
                                if (!empty($useContext)) {
                                    $context['memory_limit'] = $this->limit;
                                    $context['peak_limit']   = $peakLimit;
                                    $context['peak_usage']   = $peak['memory'];
                                }
                                if (is_string($useContext)) {
                                    $context['format'] = $useContext;
                                }
                                $this->logger->log($logLevel, 'Memory peak limit of ' . $peakLimit . ' has been exceeded by ' .
                                    $peak['memory'] - $peakLimit. ' bytes. ' . $peak['memory'] . ' bytes were used at the peak.', $context);
                            }
                        }
                    }
                }
            } else {
                throw new Exception('Error: The log level parameter was not set.');
            }
        }
    }

    /**
     * Format memory amount into readable string
     *
     * @param  int|string $memory
     * @param  int $bytes
     * @return string
     */
    public function formatMemoryToString(int|string $memory, int $bytes = 1024): string
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
     * @param  int|string $memory
     * @param  int $bytes
     * @return int
     */
    public function formatMemoryToInt(int|string $memory, int $bytes = 1024): int
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
