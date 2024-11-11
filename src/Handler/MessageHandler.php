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
 * Debug message handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.2.0
 */
class MessageHandler extends AbstractHandler
{

    /**
     * Messages
     * @var array
     */
    protected array $messages = [];

    /**
     * Add message
     *
     * @param  string $message
     * @return MessageHandler
     */
    public function addMessage(string $message): MessageHandler
    {
        $this->messages[] = ['message' => $message, 'timestamp' => (string)microtime(true)];
        return $this;
    }

    /**
     * Determine if the handler has messages
     *
     * @return bool
     */
    public function hasMessages(): bool
    {
        return (count($this->messages) > 0);
    }

    /**
     * Get messages
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        return $this->messages;
    }

    /**
     * Prepare header string
     *
     * @return string
     */
    public function prepareHeaderAsString(): string
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
    public function prepareAsString(): string
    {
        $string = '';
        foreach ($this->messages as $message) {
            $string .= number_format($message['timestamp'], 5, '.', '') . "\t" . $message['message'] . PHP_EOL;
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

            if ($logLevel !== null) {
                $context = [];
                $message = (count($this->messages) > 1) ?
                    '(' . count($this->messages) . ') new messages have been triggered.' :
                    'A new message has been triggered: ' . $this->messages[0]['message'];

                $context['messages'] = (($useContext !== null) && (($useContext !== null) && (strtolower($useContext) == 'text'))) ?
                    $this->prepareAsString() : $this->prepare();

                if (is_string($useContext)) {
                    $context['format'] = $useContext;
                }

                $this->logger->log($logLevel, $message, $context);
            } else {
                throw new Exception('Error: The log level parameter was not set.');
            }
        }
    }

}
