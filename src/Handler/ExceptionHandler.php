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
 * Debug exception handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class ExceptionHandler extends AbstractHandler
{

    /**
     * Exceptions
     * @var array
     */
    protected $exceptions = [];

    /**
     * Constructor
     *
     * Instantiate an exception handler object
     */
    public function __construct()
    {

    }

    /**
     * Add exception
     *
     * @param  \Exception $exception
     * @return self
     */
    public function addException(\Exception $exception)
    {
        $this->exceptions[microtime(true)] = $exception;
        return $this;
    }

    /**
     * Determine if the handler has exceptions
     *
     * @return boolean
     */
    public function hasExceptions()
    {
        return (count($this->exceptions) > 0);
    }

    /**
     * Get exceptions
     *
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

}
