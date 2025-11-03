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
namespace Pop\Debug\Handler;

use Pop\Log\Logger;

/**
 * Debug memory handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
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
     * Prepare handler message
     *
     * @param  ?array $context
     * @return string
     */
    public function prepareMessage(?array $context = null): string
    {
        if ($context === null) {
            $context = $this->prepare();
        }

        $message = 'Memory limit: ' . $context['limit'];
        if (!empty($context['usages'])) {
            $message .= '; ' . ((count($context['usages']) > 1) ?
                '(' . count($context['usages']) . ') memory usages have been logged.' :
                '(1) memory usage has been logged.');
        }
        if (!empty($context['peaks'])) {
            $message .= '; ' . ((count($context['peaks']) > 1) ?
                '(' . count($context['peaks']) . ') memory peaks have been logged.' :
                '(1) memory peak has been logged.');
        }

        return $message;
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
            $usageLimit = $this->loggingParams['usage_limit'] ?? null;
            $peakLimit  = $this->loggingParams['peak_limit'] ?? null;

            if ($logLevel !== null) {
                $context = $this->prepare();
                // Log general usage
                if (($usageLimit === null) && ($peakLimit === null)) {
                    foreach ($this->usages as $usage) {
                        $this->logger->log($logLevel, 'Memory Usage: ' . $usage['memory'] . ' bytes.', $usage);
                    }
                    foreach ($this->peaks as $peak) {
                        $this->logger->log($logLevel, 'Peak Memory Usage: ' . $peak['memory'] . ' bytes.', $context);
                    }
                // Log if limits are exceeded
                } else {
                    if ($usageLimit !== null)  {
                        foreach ($this->usages as $usage) {
                            if ($usage['memory'] >= $usageLimit) {
                                $this->logger->log($logLevel, 'Memory usage limit of ' . $usageLimit . ' has been exceeded by ' .
                                    $usage['memory'] - $usageLimit. ' bytes. ' . $usage['memory'] . ' bytes were used.', $usage);
                            }
                        }
                    }
                    if ($peakLimit !== null) {
                        foreach ($this->peaks as $peak) {
                            if ($peak['memory'] >= $peakLimit) {
                                $this->logger->log($logLevel, 'Memory peak limit of ' . $peakLimit . ' has been exceeded by ' .
                                    $peak['memory'] - $peakLimit. ' bytes. ' . $peak['memory'] . ' bytes were used at the peak.', $peak);
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
