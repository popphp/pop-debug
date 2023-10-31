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
namespace Pop\Debug\Handler;

use Pop\Db\Adapter\Profiler\Profiler;

/**
 * Debug query handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
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
     * @param  ?Profiler $profiler
     * @param  ?string   $name
     */
    public function __construct(?Profiler $profiler = null, ?string $name = null)
    {
        parent::__construct($name);

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
        $elapsed = $this->profiler->getElapsed();
        $data    = [
            'start'   => number_format((float)$this->profiler->getStart(), 5, '.', ''),
            'finish'  => number_format((float)$this->profiler->getFinish(), 5, '.', ''),
            'elapsed' => $elapsed,
            'steps'   => []
        ];

        foreach ($this->profiler->getSteps() as $step) {
            $data['steps'][] = [
                'start'   => number_format($step->getStart(), 5, '.', ''),
                'finish'  => number_format($step->getFinish(), 5, '.', ''),
                'elapsed' => $step->getElapsed(),
                'query'   => $step->getQuery(),
                'params'  => $step->getParams(),
                'errors'  => $step->getErrors()
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
        $string  = ((!empty($this->name)) ? $this->name . ' ' : '') . 'Query Handler';
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
        $elapsed = $this->profiler->getElapsed();
        $string  = "Start:\t\t\t" . number_format((float)$this->profiler->getStart(), 5, '.', '') . PHP_EOL;
        $string .= "Finish:\t\t\t" . number_format((float)$this->profiler->getFinish(), 5, '.', '') . PHP_EOL;
        $string .= "Elapsed:\t\t" . $elapsed . ' seconds' . PHP_EOL . PHP_EOL;

        $string .= "Queries:" . PHP_EOL;
        $string .= "--------" . PHP_EOL;
        foreach ($this->profiler->getSteps() as $step) {
            $string .= $step->getQuery() . ' [' . $step->getElapsed() . ']' . PHP_EOL;
            $string .= "Start:\t\t\t" . number_format($step->getStart(), 5, '.', '') . PHP_EOL;
            $string .= "Finish:\t\t\t" . number_format($step->getFinish(), 5, '.', '') . PHP_EOL;
            if ($step->hasParams()) {
                $string .= "Params:" . PHP_EOL;
                foreach ($step->getParams() as $name => $value) {
                    if (is_array($value)) {
                        foreach ($value as $v) {
                            $string .= "\t" . $name . ' => ' . $v . PHP_EOL;
                        }
                    } else {
                        $string .= "\t" . $name . ' => ' . $value . PHP_EOL;
                    }
                }
            }
            if ($step->hasErrors()) {
                $string .= "Errors:" . PHP_EOL;
                foreach ($step->getErrors() as $time => $error) {
                    $string .= "\t[" . number_format($time, 5, '.', '') . "]" . $error['error'] .
                        ((!empty($error['number'])) ? ' [' . $error['number'] . ']' : '') . PHP_EOL;
                }

            }
            $string .= PHP_EOL;
        }

        return $string;
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
