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

use Pop\Db\Adapter\Profiler\Profiler;
use Pop\Log\Logger;

/**
 * Debug query handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class QueryHandler extends AbstractHandler
{

    /**
     * Profiler
     * @var ?Profiler
     */
    protected ?Profiler $profiler = null;

    /**
     * Constructor
     *
     * Instantiate a query handler object
     *
     * @param ?Profiler $profiler
     * @param ?string   $name
     * @param ?Logger   $logger
     * @param array     $loggingParams
     */
    public function __construct(?Profiler $profiler = null, ?string $name = null, ?Logger $logger = null, array $loggingParams = [])
    {
        parent::__construct($name, $logger, $loggingParams);

        if ($profiler !== null) {
            $this->setProfiler($profiler);
        }
    }

    /**
     * Set profiler
     *
     * @param  Profiler $profiler
     * @return QueryHandler
     */
    public function setProfiler(Profiler $profiler): QueryHandler
    {
        $this->profiler = $profiler;
        return $this;
    }

    /**
     * Determine if the handler has a profiler
     *
     * @return bool
     */
    public function hasProfiler(): bool
    {
        return ($this->profiler !== null);
    }

    /**
     * Get profiler
     *
     * @return Profiler
     */
    public function getProfiler(): Profiler
    {
        return $this->profiler;
    }

    /**
     * Get profiler (alias method)
     *
     * @return Profiler
     */
    public function profiler(): Profiler
    {
        return $this->profiler;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        $this->setStart((float)$this->profiler->getStart());
        $this->setEnd((float)$this->profiler->getFinish());
        $this->setElapsed((float)$this->profiler->getElapsed());

        $data = [
            'start'   => number_format((float)$this->getStart(), 5, '.', ''),
            'end'     => number_format((float)$this->getEnd(), 5, '.', ''),
            'elapsed' => $this->getElapsed(),
            'steps'   => []
        ];

        foreach ($this->profiler->getSteps() as $step) {
            $data['steps'][] = [
                'start'   => number_format($step->getStart(), 5, '.', ''),
                'end'     => number_format($step->getFinish(), 5, '.', ''),
                'elapsed' => $step->getElapsed(),
                'query'   => $step->getQuery(),
                'params'  => $step->getParams(),
                'errors'  => $step->getErrors()
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

        return (!empty($context['steps']) && (count($context['steps']) > 1)) ?
            '(' . count($context['steps']) . ') new queries have been executed.' :
            '(1) new query has been executed.';
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
            $logLevel  = $this->loggingParams['level'] ?? null;
            $timeLimit = $this->loggingParams['limit'] ?? null;

            if ($logLevel !== null) {
                $context = $this->prepare();
                if ($timeLimit !== null) {
                    foreach ($context['steps'] as $step) {
                        $elapsedTime = $step->getElapsed();
                        if ($elapsedTime >= $timeLimit) {
                            $this->logger->log($logLevel, 'A query has exceeded the time limit of ' . $timeLimit .
                                ' second(s) by ' . $elapsedTime - $timeLimit . ' second(s). The query execution was a total of ' .
                                $elapsedTime . ' second(s).', $context
                            );
                        }
                    }
                } else {
                    $this->logger->log($logLevel, $this->prepareMessage($context), $context);
                }
            } else {
                throw new Exception('Error: The log level parameter was not set.');
            }
        }
    }

    /**
     * Magic get method to return the profiler.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'profiler' => $this->profiler,
            default    => null,
        };
    }

}
