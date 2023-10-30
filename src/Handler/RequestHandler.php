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

use Pop\Http\Server\Request;
use Pop\Http\Uri;
use Pop\Session\Session;

/**
 * Debug request handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class RequestHandler extends AbstractHandler
{

    /**
     * Request
     * @var ?Request
     */
    protected ?Request $request = null;

    /**
     * Request timestamp
     * @var ?float
     */
    protected ?float $requestTimestamp = null;

    /**
     * Constructor
     *
     * Instantiate a request handler object
     *
     * @param ?string  $name
     * @param ?Request $request
     */
    public function __construct(?string $name = null, ?Request $request = null)
    {
        parent::__construct($name);
        if ($request === null) {
            $request = new Request(new Uri());
        }
        $this->setRequest($request);

        $this->requestTimestamp = microtime(true);
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        Session::getInstance();

        return [
            'uri'       => $this->request->getUri()->getUri(),
            'headers'   => $this->request->getHeaders(),
            'server'    => $this->request->getServer(),
            'env'       => $this->request->getEnv(),
            'get'       => $this->request->getQuery(),
            'post'      => $this->request->getPost(),
            'put'       => $this->request->getPut(),
            'patch'     => $this->request->getPatch(),
            'delete'    => $this->request->getDelete(),
            'files'     => $this->request->getFiles(),
            'cookie'    => (isset($_COOKIE)) ? $_COOKIE : [],
            'session'   => (isset($_SESSION)) ? $_SESSION : [],
            'raw'       => $this->request->getRawData(),
            'parsed'    => $this->request->getParsedData(),
            'timestamp' => number_format($this->requestTimestamp, 5, '.', '')
        ];
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
     * Prepare header string
     *
     * @return string
     */
    public function prepareHeaderAsString(): string
    {
        $string  = ((!empty($this->name)) ? $this->name . ' ' : '') . 'Request Handler';
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
        if (!empty($this->request->getUri()) && !empty($this->request->getUri()->getUri())) {
            $string .= $this->request->getMethod() . ' ' . $this->request->getUri()->getUri() . ' [' .
                number_format($this->requestTimestamp, 5, '.', '') . ']' . PHP_EOL . PHP_EOL;

            $dataArrays = $this->prepare();

            foreach ($dataArrays as $key => $data) {
                if (is_array($data) && (count($data) > 0)) {
                    $string .= str_replace('DATA', '', strtoupper($key)) . ":" . PHP_EOL;
                    $string .= str_repeat('-', (strlen(str_replace('DATA', '', strtoupper($key))) + 1)) . PHP_EOL;
                    foreach ($data as $k => $v) {
                        $string .= $k . ": " . ((is_array($v)) ? http_build_query($v) : $v) . PHP_EOL;
                    }
                    $string .= PHP_EOL;
                }
            }
            if (!empty($this->request->getRawData())) {
                $string .= "RAW:" . PHP_EOL;
                $string .= "----" . PHP_EOL;
                $string .= $this->request->getRawData() . PHP_EOL;
            }
        } else {
            $string .= "No Request URI Detected." . PHP_EOL;
        }

        $string .= PHP_EOL;

        return $string;
    }

    /**
     * Get request timestamp
     *
     * @return float
     */
    public function getRequestTimestamp(): float
    {
        return $this->requestTimestamp;
    }

}
