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

/**
 * Debug message handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class MessageHandler extends AbstractHandler
{

    /**
     * Add message
     *
     * @param  string $message
     * @return MessageHandler
     */
    public function addMessage(string $message): MessageHandler
    {
        $this->data[] = ['message' => $message, 'timestamp' => (string)microtime(true)];
        return $this;
    }

    /**
     * Determine if the handler has messages
     *
     * @return bool
     */
    public function hasMessages(): bool
    {
        return (count($this->data) > 0);
    }

    /**
     * Get messages
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->data;
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        return $this->data;
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

        return (count($context) > 1) ?
            '(' . count($context) . ') new messages have been logged.' :
            'A new message has been logged.';
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
            $logLevel = $this->loggingParams['level'] ?? null;

            if ($logLevel !== null) {
                $context = $this->prepare();
                $this->logger->log($logLevel, $this->prepareMessage($context), $context);
            } else {
                throw new Exception('Error: The log level parameter was not set.');
            }
        }
    }

}
