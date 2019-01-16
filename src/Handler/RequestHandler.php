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
 * Debug request handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.1.0
 */
class RequestHandler extends AbstractHandler
{

    /**
     * Request URI
     * @var string
     */
    protected $requestUri = null;

    /**
     * Headers
     * @var array
     */
    protected $headers = [];

    /**
     * SERVER array
     */
    protected $server = [];

    /**
     * ENV array
     */
    protected $env    = [];

    /**
     * GET array
     */
    protected $get    = [];

    /**
     * POST array
     */
    protected $post   = [];

    /**
     * PUT array
     */
    protected $put    = [];

    /**
     * PATCH array
     */
    protected $patch  = [];

    /**
     * DELETE array
     */
    protected $delete = [];

    /**
     * FILES array
     */
    protected $files  = [];

    /**
     * COOKIE array
     */
    protected $cookie = [];

    /**
     * SESSION array
     */
    protected $session = [];

    /**
     * Raw data
     * @var string
     */
    protected $rawData = null;

    /**
     * Parsed data
     * @var mixed
     */
    protected $parsedData = null;

    /**
     * Request timestamp
     * @var float
     */
    protected $requestTimestamp = null;

    /**
     * Constructor
     *
     * Instantiate a request handler object
     *
     * @param string  $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->requestTimestamp = microtime(true);

        $this->server  = (isset($_SERVER))  ? $_SERVER  : [];
        $this->env     = (isset($_ENV))     ? $_ENV     : [];
        $this->get     = (isset($_GET))     ? $_GET     : [];
        $this->post    = (isset($_POST))    ? $_POST    : [];
        $this->files   = (isset($_FILES))   ? $_FILES   : [];
        $this->cookie  = (isset($_COOKIE))  ? $_COOKIE  : [];
        $this->session = (isset($_SESSION)) ? $_SESSION : [];

        if (isset($_SERVER['REQUEST_URI'])) {
            $this->requestUri = $_SERVER['REQUEST_URI'];
        }

        // Get any possible request headers
        if (function_exists('getallheaders')) {
            $this->headers = getallheaders();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) == 'HTTP_') {
                    $key = ucfirst(strtolower(str_replace('HTTP_', '', $key)));
                    if (strpos($key, '_') !== false) {
                        $ary = explode('_', $key);
                        foreach ($ary as $k => $v){
                            $ary[$k] = ucfirst(strtolower($v));
                        }
                        $key = implode('-', $ary);
                    }
                    $this->headers[$key] = $value;
                }
            }
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->parseData();
        }
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare()
    {
        $data = [
            'uri'       => $this->requestUri,
            'headers'   => $this->headers,
            'server'    => $this->server,
            'env'       => $this->env,
            'get'       => $this->get,
            'post'      => $this->post,
            'put'       => $this->put,
            'patch'     => $this->patch,
            'delete'    => $this->delete,
            'files'     => $this->files,
            'cookie'    => $this->cookie,
            'session'   => $this->session,
            'raw'       => $this->rawData,
            'parsed'    => $this->parsedData,
            'timestamp' => number_format($this->requestTimestamp, 5, '.', '')
        ];

        return $data;
    }

    /**
     * Prepare header string
     *
     * @return string
     */
    public function prepareHeaderAsString()
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
    public function prepareAsString()
    {
        $string = '';
        if (!empty($this->requestUri)) {
            $string .= "URI: " . $this->requestUri . ' [' . number_format($this->requestTimestamp, 5, '.', '') . ']' . PHP_EOL;
            if (count($this->headers) > 0) {
                $string .= PHP_EOL;
                $string .= "HEADERS:" . PHP_EOL;
                $string .= "--------" . PHP_EOL;
                foreach ($this->headers as $header => $value) {
                    $string .= $header . ": " . $value . PHP_EOL;
                }
                $string .= PHP_EOL;
            }
            $dataArrays = ['server', 'env', 'cookie', 'session', 'get', 'post', 'put', 'patch', 'delete', 'files', 'parsedData'];
            foreach ($dataArrays as $data) {
                if (count($this->{$data}) > 0) {
                    $string .= str_replace('DATA', '', strtoupper($data)) . ":" . PHP_EOL;
                    $string .= str_repeat('-', (strlen(str_replace('DATA', '', strtoupper($data))) + 1)) . PHP_EOL;
                    foreach ($this->{$data} as $key => $value) {
                        $string .= $key . ": " . ((is_array($value)) ? http_build_query($value) : $value) . PHP_EOL;
                    }
                    $string .= PHP_EOL;
                }
            }
            if (!empty($this->rawData)) {
                $string .= "RAW:" . PHP_EOL;
                $string .= "----" . PHP_EOL;
                $string .= $this->rawData . PHP_EOL;
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
    public function getRequestTimestamp()
    {
        return $this->requestTimestamp;
    }

    /**
     * Return whether or not the request has FILES
     *
     * @return boolean
     */
    public function hasFiles()
    {
        return (count($this->files) > 0);
    }

    /**
     * Return whether or not the method is GET
     *
     * @return boolean
     */
    public function isGet()
    {
        return (isset($this->server['REQUEST_METHOD']) && ($this->server['REQUEST_METHOD'] == 'GET'));
    }

    /**
     * Return whether or not the method is HEAD
     *
     * @return boolean
     */
    public function isHead()
    {
        return (isset($this->server['REQUEST_METHOD']) && ($this->server['REQUEST_METHOD'] == 'HEAD'));
    }

    /**
     * Return whether or not the method is POST
     *
     * @return boolean
     */
    public function isPost()
    {
        return (isset($this->server['REQUEST_METHOD']) && ($this->server['REQUEST_METHOD'] == 'POST'));
    }

    /**
     * Return whether or not the method is PUT
     *
     * @return boolean
     */
    public function isPut()
    {
        return (isset($this->server['REQUEST_METHOD']) && ($this->server['REQUEST_METHOD'] == 'PUT'));
    }

    /**
     * Return whether or not the method is DELETE
     *
     * @return boolean
     */
    public function isDelete()
    {
        return (isset($this->server['REQUEST_METHOD']) && ($this->server['REQUEST_METHOD'] == 'DELETE'));
    }

    /**
     * Return whether or not the method is TRACE
     *
     * @return boolean
     */
    public function isTrace()
    {
        return (isset($this->server['REQUEST_METHOD']) && ($this->server['REQUEST_METHOD'] == 'TRACE'));
    }

    /**
     * Return whether or not the method is OPTIONS
     *
     * @return boolean
     */
    public function isOptions()
    {
        return (isset($this->server['REQUEST_METHOD']) && ($this->server['REQUEST_METHOD'] == 'OPTIONS'));
    }

    /**
     * Return whether or not the method is CONNECT
     *
     * @return boolean
     */
    public function isConnect()
    {
        return (isset($this->server['REQUEST_METHOD']) && ($this->server['REQUEST_METHOD'] == 'CONNECT'));
    }

    /**
     * Return whether or not the method is PATCH
     *
     * @return boolean
     */
    public function isPatch()
    {
        return (isset($this->server['REQUEST_METHOD']) && ($this->server['REQUEST_METHOD'] == 'PATCH'));
    }

    /**
     * Return whether or not the request is secure
     *
     * @return boolean
     */
    public function isSecure()
    {
        return (isset($this->server['HTTPS']) || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443')));
    }

    /**
     * Get the request URI
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * Get the document root
     *
     * @return string
     */
    public function getDocumentRoot()
    {
        return (isset($this->server['DOCUMENT_ROOT'])) ? $this->server['DOCUMENT_ROOT'] : null;
    }

    /**
     * Get the method
     *
     * @return string
     */
    public function getMethod()
    {
        return (isset($this->server['REQUEST_METHOD'])) ? $this->server['REQUEST_METHOD'] : null;
    }

    /**
     * Get the server port
     *
     * @return string
     */
    public function getPort()
    {
        return (isset($this->server['SERVER_PORT'])) ? $this->server['SERVER_PORT'] : null;
    }

    /**
     * Get scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return ($this->isSecure()) ? 'https' : 'http';
    }

    /**
     * Get host without port)
     *
     * @return string
     */
    public function getHost()
    {
        $hostname = null;

        if (!empty($this->server['HTTP_HOST'])) {
            $hostname = $this->server['HTTP_HOST'];
        } else if (!empty($this->server['SERVER_NAME'])) {
            $hostname =$this->server['SERVER_NAME'];
        }

        if (strpos($hostname, ':') !== false) {
            $hostname = substr($hostname, 0, strpos($hostname, ':'));
        }

        return $hostname;
    }

    /**
     * Get host with port
     *
     * @return string
     */
    public function getFullHost()
    {
        $port     = $this->getPort();
        $hostname = null;

        if (!empty($this->server['HTTP_HOST'])) {
            $hostname = $this->server['HTTP_HOST'];
        } else if (!empty($this->server['SERVER_NAME'])) {
            $hostname =$this->server['SERVER_NAME'];
        }

        if ((strpos($hostname, ':') === false) && (null !== $port)) {
            $hostname .= ':' . $port;
        }

        return $hostname;
    }

    /**
     * Get client's IP
     *
     * @param  boolean $proxy
     * @return string
     */
    public function getIp($proxy = true)
    {
        $ip = null;

        if ($proxy && isset($this->server['HTTP_CLIENT_IP'])) {
            $ip = $this->server['HTTP_CLIENT_IP'];
        } else if ($proxy && isset($this->server['HTTP_X_FORWARDED_FOR'])) {
            $ip = $this->server['HTTP_X_FORWARDED_FOR'];
        } else if (isset($this->server['REMOTE_ADDR'])) {
            $ip = $this->server['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Get a value from $_GET, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getQuery($key = null)
    {
        if (null === $key) {
            return $this->get;
        } else {
            return (isset($this->get[$key])) ? $this->get[$key] : null;
        }
    }

    /**
     * Get a value from $_POST, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getPost($key = null)
    {
        if (null === $key) {
            return $this->post;
        } else {
            return (isset($this->post[$key])) ? $this->post[$key] : null;
        }
    }
    /**
     * Get a value from PUT query data, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getPut($key = null)
    {
        if (null === $key) {
            return $this->put;
        } else {
            return (isset($this->put[$key])) ? $this->put[$key] : null;
        }
    }

    /**
     * Get a value from PATCH query data, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getPatch($key = null)
    {
        if (null === $key) {
            return $this->patch;
        } else {
            return (isset($this->patch[$key])) ? $this->patch[$key] : null;
        }
    }

    /**
     * Get a value from DELETE query data, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getDelete($key = null)
    {
        if (null === $key) {
            return $this->delete;
        } else {
            return (isset($this->delete[$key])) ? $this->delete[$key] : null;
        }
    }

    /**
     * Get a value from $_FILES, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getFiles($key = null)
    {
        if (null === $key) {
            return $this->files;
        } else {
            return (isset($this->files[$key])) ? $this->files[$key] : null;
        }
    }

    /**
     * Get a value from $_SERVER, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getServer($key = null)
    {
        if (null === $key) {
            return $this->server;
        } else {
            return (isset($this->server[$key])) ? $this->server[$key] : null;
        }
    }

    /**
     * Get a value from $_ENV, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getEnv($key = null)
    {
        if (null === $key) {
            return $this->env;
        } else {
            return (isset($this->env[$key])) ? $this->env[$key] : null;
        }
    }

    /**
     * Get a value from $_COOKIE, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getCookie($key = null)
    {
        if (null === $key) {
            return $this->cookie;
        } else {
            return (isset($this->cookie[$key])) ? $this->cookie[$key] : null;
        }
    }

    /**
     * Get a value from $_SESSION, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getSession($key = null)
    {
        if (null === $key) {
            return $this->session;
        } else {
            return (isset($this->session[$key])) ? $this->session[$key] : null;
        }
    }

    /**
     * Get a value from parsed data, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getParsedData($key = null)
    {
        $result = null;

        if ((null !== $this->parsedData) && is_array($this->parsedData)) {
            if (null === $key) {
                $result = $this->parsedData;
            } else {
                $result = (isset($this->parsedData[$key])) ? $this->parsedData[$key] : null;
            }
        }

        return $result;
    }

    /**
     * Get the raw data
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * Get a value from the request headers
     *
     * @param  string $key
     * @return string
     */
    public function getHeader($key)
    {
        return (isset($this->headers[$key])) ? $this->headers[$key] : null;
    }

    /**
     * Get the request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Parse any data that came with the request
     *
     * @return void
     */
    protected function parseData()
    {
        if (strtoupper($this->getMethod()) == 'GET') {
            $this->rawData = (isset($_SERVER['QUERY_STRING'])) ? rawurldecode($_SERVER['QUERY_STRING']) : null;
        } else {
            $input = fopen('php://input', 'r');
            while ($data = fread($input, 1024)) {
                $this->rawData .= $data;
            }
        }

        // If the content-type is JSON
        if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'json') !== false)) {
            $this->parsedData = json_decode($this->rawData, true);
        // Else, if the content-type is XML
        } else if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'xml') !== false)) {
            $matches = [];
            preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $this->rawData, $matches);

            foreach ($matches[0] as $match) {
                $strip = str_replace(
                    ['<![CDATA[', ']]>', '<', '>'],
                    ['', '', '&lt;', '&gt;'],
                    $match
                );
                $this->rawData = str_replace($match, $strip, $this->rawData);
            }

            $this->parsedData = json_decode(json_encode((array)simplexml_load_string($this->rawData)), true);
        // Else, default to a regular URL-encoded string
        } else {
            switch (strtoupper($this->getMethod())) {
                case 'GET':
                    $this->parsedData = $this->get;
                    break;

                case 'POST':
                    $this->parsedData = $this->post;
                    break;
                default:
                    if (isset($_SERVER['CONTENT_TYPE']) && (strtolower($_SERVER['CONTENT_TYPE']) == 'application/x-www-form-urlencoded')) {
                        parse_str($this->rawData, $this->parsedData);
                    }
            }
        }

        switch (strtoupper($this->getMethod())) {
            case 'PUT':
                $this->put = $this->parsedData;
                break;

            case 'PATCH':
                $this->patch = $this->parsedData;
                break;

            case 'DELETE':
                $this->delete = $this->parsedData;
                break;
        }
    }

}
