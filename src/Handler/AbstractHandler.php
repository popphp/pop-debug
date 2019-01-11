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
 * Debug handler abstract class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.0
 */
abstract class AbstractHandler implements HandlerInterface
{

    /**
     * Name of time measurement
     * @var string
     */
    protected $name = null;

    /**
     * Constructor
     *
     * Instantiate a handler object
     *
     * @param string  $name
     */
    public function __construct($name = null)
    {
        if (null !== $name) {
            $this->setName($name);
        }
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return AbstractHandler
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    abstract public function prepare();

    /**
     * Prepare header string
     *
     * @return string
     */
    abstract public function prepareHeaderAsString();

    /**
     * Prepare handler data as string
     *
     * @return string
     */
    abstract public function prepareAsString();

}
