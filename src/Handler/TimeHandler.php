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

use Pop\Log\Logger;

/**
 * Debug time handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class TimeHandler extends AbstractHandler
{

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        if ($this->end === null) {
            $this->setEnd();
        }

        if (!$this->hasData()) {
            $this->setData([
                'start'   => number_format($this->start, 5, '.', ''),
                'end'     => number_format($this->end, 5, '.', ''),
                'elapsed' => $this->getElapsed()
            ]);
        }

        return $this->getData();
    }

    /**
     * Prepare handler message
     *
     * @param  ?array $context
     * @return string
     */
    public function prepareMessage(?array $context = null): string
    {
        $elapsedTime = $this->getElapsed();
        return (!empty($elapsedTime)) ?
            'A new ' . $elapsedTime . ' second event has been triggered.' :
            'A new timed event has been triggered.';
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
                $elapsedTime = $this->getElapsed();
                $context     = $this->prepare();
                if ($timeLimit !== null) {
                    if ($elapsedTime >= $timeLimit) {
                        $context['time_limit'] = $timeLimit;
                        $this->logger->log(
                            $logLevel, 'The time limit of '. $timeLimit . ' second(s) has been exceeded by ' .
                            $elapsedTime - $timeLimit . ' second(s). The timed event was a total of ' .
                            $elapsedTime . ' second(s).', $context
                        );
                    }
                } else {
                    $this->logger->log($logLevel, $this->prepareMessage(), $context);
                }
            } else {
                throw new Exception('Error: The log level parameter was not set.');
            }
        }
    }

}
