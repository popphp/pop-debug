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

/**
 * Debug handler abstract class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
abstract class AbstractHandler implements HandlerInterface
{

    /**
     * Name of time measurement
     * @var ?string
     */
    protected ?string $name = null;

    /**
     * Constructor
     *
     * Instantiate a handler object
     *
     * @param ?string $name
     */
    public function __construct(?string $name = null)
    {
        if ($name !== null) {
            $this->setName($name);
        }
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return AbstractHandler
     */
    public function setName(string $name): AbstractHandler
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    abstract public function prepare(): array;

    /**
     * Prepare header string
     *
     * @return string
     */
    abstract public function prepareHeaderAsString(): string;

    /**
     * Prepare handler data as string
     *
     * @return string
     */
    abstract public function prepareAsString(): string;

}
