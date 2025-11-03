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
namespace Pop\Debug\Storage;

use Pop\Debug\Handler\AbstractHandler;

/**
 * Debug storage abstract class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
abstract class AbstractStorage implements StorageInterface
{


    /**
     * Prepare events
     *
     * @param  string          $id
     * @param  string          $name
     * @param  AbstractHandler $handler
     * @return array
     */
    public function prepareEvents(string $id, string $name, AbstractHandler $handler): array
    {
        $events       = [];
        $handlerClass = get_class($handler);

        switch ($handlerClass) {
            case 'Pop\Debug\Handler\ExceptionHandler':
                $data = $handler->prepare();
                foreach ($data as $datum) {
                    $events[] = [
                        'key'       => $id,
                        'handler'   => $name,
                        'start'     => $datum['timestamp'],
                        'end'       => null,
                        'elapsed'   => null,
                        'type'      => (is_string($datum['exception']) ? $datum['exception'] : get_class($datum['exception'])),
                        'message'   => (is_array($datum) && isset($datum['message']) ? $datum['message'] : $datum['exception']->getMessage()),
                        'context'   => (is_array($datum) && isset($datum['message']) ? json_encode($datum) : json_encode($data)),
                    ];
                }
                break;
            case 'Pop\Debug\Handler\MemoryHandler':
                $data = $handler->prepare();
                $events[] = [
                    'key'       => $id,
                    'handler'   => $name,
                    'start'     => $handler->getStart(),
                    'end'       => null,
                    'elapsed'   => null,
                    'type'      => 'limit',
                    'message'   => $data['limit'],
                    'context'   => json_encode($handler->prepare()),
                ];
                foreach ($data['usages'] as $usage) {
                    $events[] = [
                        'key'       => $id,
                        'handler'   => $name,
                        'start'     => $usage['timestamp'],
                        'end'       => null,
                        'elapsed'   => null,
                        'type'      => 'usage',
                        'message'   => $usage['memory'],
                        'context'   => null,
                    ];
                }
                foreach ($data['peaks'] as $peak) {
                    $events[] = [
                        'key'       => $id,
                        'handler'   => $name,
                        'start'     => $peak['timestamp'],
                        'end'       => null,
                        'elapsed'   => null,
                        'type'      => 'peak',
                        'message'   => $peak['memory'],
                        'context'   => null,
                    ];
                }
                break;
            case 'Pop\Debug\Handler\MessageHandler':
                $data = $handler->prepare();
                foreach ($data as $datum) {
                    $events[] = [
                        'key'       => $id,
                        'handler'   => $name,
                        'start'     => $datum['timestamp'],
                        'end'       => null,
                        'elapsed'   => null,
                        'type'      => 'message',
                        'message'   => $datum['message'],
                        'context'   => null,
                    ];
                }
                break;
            case 'Pop\Debug\Handler\QueryHandler':
                $data = $handler->prepare();
                $events[] = [
                    'key'       => $id,
                    'handler'   => $name,
                    'start'     => $data['start'] ?? $handler->getStart(),
                    'end'       => $data['end'] ?? $handler->getEnd(),
                    'elapsed'   => $data['elapsed'] ?? $handler->getElapsed(),
                    'type'      => 'query-set',
                    'message'   => $handler->prepareMessage(),
                    'context'   => json_encode($handler->prepare()),
                ];
                foreach ($data['steps'] as $step) {
                    $events[] = [
                        'key'       => $id,
                        'handler'   => $name,
                        'start'     => $step['start'] ?? null,
                        'end'       => $step['end'] ?? null,
                        'elapsed'   => $step['elapsed'] ?? null,
                        'type'      => 'query',
                        'message'   => $data['query'] ?? null,
                        'context'   => json_encode($data),
                    ];
                }
                break;
            // Pop\Debug\Handler\PhpHandler
            // Pop\Debug\Handler\RequestHandler
            // Pop\Debug\Handler\TimeHandler
            default:
                $data = $handler->prepare();
                $events[] = [
                    'key'       => $id,
                    'handler'   => $name,
                    'start'     => $handler->getStart(),
                    'end'       => $handler->getEnd(),
                    'elapsed'   => $handler->getElapsed(),
                    'type'      => null,
                    'message'   => $handler->prepareMessage(),
                    'context'   => json_encode($data),
                ];
        }

        return $events;
    }

    /**
     * Save debug data
     *
     * @param  string          $id
     * @param  string          $name
     * @param  AbstractHandler $handler
     * @return void
     */
    abstract public function save(string $id, string $name, AbstractHandler $handler): void;

    /**
     * Clear all debug data
     *
     * @return void
     */
    abstract public function clear(): void;

}
