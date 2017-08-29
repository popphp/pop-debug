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

use Pop\Db\Adapter\Profiler\Profiler;

/**
 * Debug query handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class QueryHandler extends AbstractHandler
{

    /**
     * Profiler
     * @var Profiler
     */
    protected $profiler = null;

    /**
     * Constructor
     *
     * Instantiate a query handler object
     *
     * @param  Profiler $profiler
     */
    public function __construct(Profiler $profiler = null)
    {
        if (null !== $profiler) {
            $this->setProfiler($profiler);
        }
    }

    /**
     * Set profiler
     *
     * @param  Profiler $profiler
     * @return self
     */
    public function setProfiler(Profiler $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }

    /**
     * Determine if the handler has a profiler
     *
     * @return boolean
     */
    public function hasProfiler()
    {
        return (null !== $this->profiler);
    }

    /**
     * Get profiler
     *
     * @return Profiler
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * Get profiler (alias method)
     *
     * @return Profiler
     */
    public function profiler()
    {
        return $this->profiler;
    }

    /**
     * Magic get method to return the profiler.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'profiler':
                return $this->profiler;
                break;
            default:
                return null;
        }
    }

}
