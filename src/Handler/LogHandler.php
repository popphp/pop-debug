<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Handler;

use Pop\Log\Logger;

/**
 * Debug query handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.0.2
 */
class LogHandler extends AbstractHandler
{

    /**
     * Logger
     * @var Logger
     */
    protected $logger = null;

    /**
     * Entries
     * @var array
     */
    protected $entries = [];

    /**
     * Constructor
     *
     * Instantiate a log handler object
     *
     * @param  Logger $logger
     * @param  string $name
     */
    public function __construct(Logger $logger = null, $name = null)
    {
        parent::__construct($name);

        if (null !== $logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * Set logger
     *
     * @param  Logger $logger
     * @return self
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Determine if the handler has a logger
     *
     * @return boolean
     */
    public function hasLogger()
    {
        return (null !== $this->logger);
    }

    /**
     * Get logger
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Get logger (alias method)
     *
     * @return Logger
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * Add an EMERGENCY log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return LogHandler
     */
    public function emergency($message, array $context = [])
    {
        return $this->log(Logger::EMERGENCY, $message, $context);
    }

    /**
     * Add an ALERT log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return LogHandler
     */
    public function alert($message, array $context = [])
    {
        return $this->log(Logger::ALERT, $message, $context);
    }

    /**
     * Add a CRITICAL log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return LogHandler
     */
    public function critical($message, array $context = [])
    {
        return $this->log(Logger::CRITICAL, $message, $context);
    }

    /**
     * Add an ERROR log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return LogHandler
     */
    public function error($message, array $context = [])
    {
        return $this->log(Logger::ERROR, $message, $context);
    }

    /**
     * Add a WARNING log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return LogHandler
     */
    public function warning($message, array $context = [])
    {
        return $this->log(Logger::WARNING, $message, $context);
    }

    /**
     * Add a NOTICE log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return LogHandler
     */
    public function notice($message, array $context = [])
    {
        return $this->log(Logger::NOTICE, $message, $context);
    }

    /**
     * Add an INFO log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return LogHandler
     */
    public function info($message, array $context = [])
    {
        return $this->log(Logger::INFO, $message, $context);
    }

    /**
     * Add a DEBUG log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return LogHandler
     */
    public function debug($message, array $context = [])
    {
        return $this->log(Logger::DEBUG, $message, $context);
    }

    /**
     * Add a log entry
     *
     * @param  mixed $level
     * @param  mixed $message
     * @param  array $context
     * @return LogHandler
     */
    public function log($level, $message, array $context = [])
    {

        $this->logger->log($level, $message, $context);
        $this->entries[microtime(true)] = $this->logger->getLevel($level) . "\t" . (string)$message;
        return $this;
    }

    /**
     * Determine if the handler has entries
     *
     * @return boolean
     */
    public function hasEntries()
    {
        return (count($this->entries) > 0);
    }

    /**
     * Get entries
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->entries;
    }


    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare()
    {
        $data = [];

        foreach ($this->entries as $time => $entry) {
            $data[number_format($time, 5, '.', '')] = $entry;
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
        $string  = ((!empty($this->name)) ? $this->name . ' ' : '') . 'Log Handler';
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
        foreach ($this->entries as $time => $entry) {
            $string .= number_format($time, 5, '.', '') . "\t" . $entry . PHP_EOL;
        }
        $string .= PHP_EOL;

        return $string;
    }

    /**
     * Magic get method to return the logger.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'logger':
                return $this->logger;
                break;
            default:
                return null;
        }
    }

}
