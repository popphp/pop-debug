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

use Pop\Http\Server\Request;
use Pop\Http\Uri;
use Pop\Log\Logger;
use Pop\Session\Session;

/**
 * Debug request handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class RequestHandler extends AbstractHandler
{

    /**
     * Request
     * @var ?Request
     */
    protected ?Request $request = null;

    /**
     * Constructor
     *
     * Instantiate a request handler object
     *
     * @param ?Request $request
     * @param ?string  $name
     */
    public function __construct(?Request $request = null, ?string $name = null, ?Logger $logger = null, array $loggingParams = [])
    {
        parent::__construct($name, $logger, $loggingParams);
        if ($request === null) {
            $request = new Request(new Uri());
        }
        $this->setRequest($request);
    }

    /**
     * Set request
     *
     * @param  Request $request
     * @return RequestHandler
     */
    public function setRequest(Request $request): RequestHandler
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get request
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get request (alias)
     *
     * @return Request
     */
    public function request(): Request
    {
        return $this->request;
    }

    /**
     * Has request
     *
     * @return bool
     */
    public function hasRequest(): bool
    {
        return ($this->request !== null);
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        Session::getInstance();

        if (!$this->hasEnd()) {
            $this->setEnd();
        }

        $requestData = [
            'uri'     => $this->request->getUri()->getUri(),
            'method'  => $this->request->getMethod(),
            'headers' => $this->request->getHeaders(),
            'server'  => $this->request->getServer(),
            'env'     => $this->request->getEnv(),
            'get'     => $this->request->getQuery(),
            'post'    => $this->request->getPost(),
            'put'     => $this->request->getPut(),
            'patch'   => $this->request->getPatch(),
            'delete'  => $this->request->getDelete(),
            'files'   => $this->request->getFiles(),
            'cookie'  => (isset($_COOKIE)) ? $_COOKIE : [],
            'session' => (isset($_SESSION)) ? $_SESSION : [],
            'raw'     => $this->request->getRawData(),
            'parsed'  => $this->request->getParsedData(),
        ];

        return $requestData;
    }

    /**
     * Prepare handler message
     *
     * @param  ?array $context
     * @return string
     */
    public function prepareMessage(?array $context = null): string
    {
        return (!empty($this->request)) ?
            "The HTTP request '" .  $this->request->getUri()->getUri() . "' was received." :
            "An HTTP request was received.";
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
                $context = $this->prepare();
                if ($timeLimit !== null) {
                    $elapsedTime = $this->getElapsed();
                    if ($elapsedTime >= $timeLimit) {
                        $this->logger->log($logLevel, 'The request \'' . $this->request->getUri()->getUri() .
                            '\' has exceeded the time limit of ' . $timeLimit . ' second(s) by ' .
                            $elapsedTime - $timeLimit . ' second(s). The request was a total of ' . $elapsedTime . ' second(s).',
                            $context
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
