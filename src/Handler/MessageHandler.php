<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Handler;

/**
 * Debug message handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
class MessageHandler extends AbstractHandler
{

    /**
     * Messages
     * @var array
     */
    protected $messages = [];

    /**
     * Add message
     *
     * @param  string $message
     * @return self
     */
    public function addMessage($message)
    {
        $this->messages[microtime(true)] = $message;
        return $this;
    }

    /**
     * Determine if the handler has messages
     *
     * @return boolean
     */
    public function hasMessages()
    {
        return (count($this->messages) > 0);
    }

    /**
     * Get messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare()
    {
        $data = [];

        foreach ($this->messages as $time => $message) {
            $data[number_format($time, 5, '.', '')] = $message;
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
        $string  = ((!empty($this->name)) ? $this->name . ' ' : '') . 'Message Handler';
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
        $string = '';
        foreach ($this->messages as $time => $message) {
            $string .= number_format($time, 5, '.', '') . "\t" . $message . PHP_EOL;
        }
        $string .= PHP_EOL;

        return $string;
    }

}
