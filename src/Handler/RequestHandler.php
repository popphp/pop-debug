<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Handler;

use Pop\Http\Server\Request;
use Pop\Http\Uri;
use Pop\Session\Session;
use Psr\Log\LoggerInterface;

/**
 * Debug request handler class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class RequestHandler extends AbstractHandler
{

    /**
     * Request
     * @var ?Request
     */
    protected ?Request $request = null;

    /**
     * Default keys (case-insensitive, separator-insensitive substring match) whose values get redacted
     * @var array
     */
    protected const array DEFAULT_REDACTED_KEYS = [
        'pass', 'pwd', 'secret', 'token', 'apikey', 'accesstoken', 'refreshtoken',
        'clientsecret', 'privatekey', 'authorization', 'auth', 'cookie', 'csrf',
        'xsrf', 'sessionid', 'creditcard', 'cardnumber', 'cvv', 'cvc', 'ssn', 'pin',
    ];

    /**
     * Value substituted in for anything matched by $redactedKeys or held in $_COOKIE/$_SESSION
     * @var string
     */
    protected const string REDACTED_VALUE = '[REDACTED]';

    /**
     * Whether to redact sensitive request data (headers, server/env vars, post/put/patch/parsed
     * data matching $redactedKeys, plus the entirety of $_COOKIE and $_SESSION) before it is
     * returned from prepare() and, in turn, logged or written to storage. Defaults to true so
     * secrets aren't captured in plaintext by default.
     * @var bool
     */
    protected bool $redactSensitiveData = true;

    /**
     * Keys whose values are redacted when $redactSensitiveData is true
     * @var array
     */
    protected array $redactedKeys = self::DEFAULT_REDACTED_KEYS;

    /**
     * Cached, normalized (lowercased, non-alphanumeric stripped) version of $redactedKeys,
     * rebuilt lazily on next use whenever $redactedKeys changes
     * @var ?array
     */
    protected ?array $normalizedRedactedKeys = null;

    /**
     * Constructor
     *
     * Instantiate a request handler object
     *
     * @param ?Request         $request
     * @param ?string          $name
     * @param ?LoggerInterface $logger
     * @param array            $loggingParams
     */
    public function __construct(?Request $request = null, ?string $name = null, ?LoggerInterface $logger = null, array $loggingParams = [])
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
     * Set whether to redact sensitive request data before it's returned from prepare()
     *
     * @param  bool $redact
     * @return RequestHandler
     */
    public function setRedactSensitiveData(bool $redact = true): RequestHandler
    {
        $this->redactSensitiveData = $redact;
        return $this;
    }

    /**
     * Determine if sensitive request data is being redacted
     *
     * @return bool
     */
    public function isRedactingSensitiveData(): bool
    {
        return $this->redactSensitiveData;
    }

    /**
     * Set the keys (case-insensitive, separator-insensitive substring match) whose values get redacted
     *
     * @param  array $keys
     * @return RequestHandler
     */
    public function setRedactedKeys(array $keys): RequestHandler
    {
        $this->redactedKeys           = $keys;
        $this->normalizedRedactedKeys = null;
        return $this;
    }

    /**
     * Add a key whose value should be redacted
     *
     * @param  string $key
     * @return RequestHandler
     */
    public function addRedactedKey(string $key): RequestHandler
    {
        $this->redactedKeys[]         = $key;
        $this->normalizedRedactedKeys = null;
        return $this;
    }

    /**
     * Get the keys whose values get redacted
     *
     * @return array
     */
    public function getRedactedKeys(): array
    {
        return $this->redactedKeys;
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

        $headers = $this->request->getHeaders();
        $server  = $this->request->getServer();
        $env     = $this->request->getEnv();
        $get     = $this->request->getQuery();
        $post    = $this->request->getPost();
        $put     = $this->request->getPut();
        $patch   = $this->request->getPatch();
        $delete  = $this->request->getDelete();
        $cookie  = $_COOKIE;
        $session = (isset($_SESSION)) ? $_SESSION : [];
        $parsed  = $this->request->getParsedData();

        if ($this->redactSensitiveData) {
            $headers = $this->redactKeys($headers);
            $server  = $this->redactKeys($server);
            $env     = $this->redactKeys($env);
            $get     = $this->redactKeys($get);
            $post    = $this->redactKeys($post);
            $put     = $this->redactKeys($put);
            $patch   = $this->redactKeys($patch);
            $delete  = $this->redactKeys($delete);
            $cookie  = $this->redactAll($cookie);
            $session = $this->redactAll($session);
            $parsed  = $this->redactKeys($parsed);
        }

        return [
            'uri'     => $this->request->getUri()->getUri(),
            'method'  => $this->request->getMethod(),
            'headers' => $headers,
            'server'  => $server,
            'env'     => $env,
            'get'     => $get,
            'post'    => $post,
            'put'     => $put,
            'patch'   => $patch,
            'delete'  => $delete,
            'files'   => $this->request->getFiles(),
            'cookie'  => $cookie,
            'session' => $session,
            'raw'     => $this->request->getRawData(),
            'parsed'  => $parsed,
        ];
    }

    /**
     * Recursively redact array values whose key matches one of $redactedKeys
     *
     * @param  mixed $data
     * @return mixed
     */
    protected function redactKeys(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if ($this->isRedactedKey((string)$key)) {
                $data[$key] = self::REDACTED_VALUE;
            } else if (is_array($value)) {
                $data[$key] = $this->redactKeys($value);
            }
        }

        return $data;
    }

    /**
     * Redact every value in a flat array, regardless of key (used for $_COOKIE/$_SESSION,
     * whose contents are treated as sensitive-by-nature)
     *
     * @param  mixed $data
     * @return mixed
     */
    protected function redactAll(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $data[$key] = self::REDACTED_VALUE;
        }

        return $data;
    }

    /**
     * Determine if a key matches one of $redactedKeys (case- and separator-insensitive)
     *
     * @param  string $key
     * @return bool
     */
    protected function isRedactedKey(string $key): bool
    {
        if ($this->normalizedRedactedKeys === null) {
            $this->normalizedRedactedKeys = [];
            foreach ($this->redactedKeys as $redactedKey) {
                $normalizedRedactedKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$redactedKey));
                if ($normalizedRedactedKey !== '') {
                    $this->normalizedRedactedKeys[] = $normalizedRedactedKey;
                }
            }
        }

        $normalizedKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $key));

        foreach ($this->normalizedRedactedKeys as $normalizedRedactedKey) {
            if (str_contains($normalizedKey, $normalizedRedactedKey)) {
                return true;
            }
        }

        return false;
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
        $logLevel = $this->resolveLogLevel();
        if ($logLevel === null) {
            return;
        }

        $timeLimit = $this->loggingParams['limit'] ?? null;
        $context   = $this->prepare();

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
    }

}
