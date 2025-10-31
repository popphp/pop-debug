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
class PhpHandler extends AbstractHandler
{

    /**
     * PHP version
     * @var string
     */
    protected string $version = PHP_VERSION;
    /**
     * PHP major version
     * @var int
     */
    protected int $majorVersion = PHP_MAJOR_VERSION;

    /**
     * PHP minor version
     * @var int
     */
    protected int $minorVersion = PHP_MINOR_VERSION;

    /**
     * PHP release version
     * @var int
     */
    protected int $releaseVersion = PHP_RELEASE_VERSION;

    /**
     * PHP version extra
     * @var string
     */
    protected string $extraVersion = PHP_EXTRA_VERSION;

    /**
     * PHP date/time
     * @var ?string
     */
    protected ?string $dateTime = null;

    /**
     * PHP error settings
     * @var array
     */
    protected array $errorSettings = [
        'error_reporting_list'   => [],
        'error_reporting'        => null,
        'display_errors'         => null,
        'display_startup_errors' => null,
        'log_errors'             => null,
    ];

    /**
     * PHP limits
     * @var array
     */
    protected array $limits = [
        'max_execution_time'      => null,
        'max_input_time'          => null,
        'max_input_nesting_level' => null,
        'max_input_vars'          => null,
        'post_max_size'           => null,
        'file_uploads'            => null,
        'upload_max_filesize'     => null,
        'max_file_uploads'        => null,
    ];

    /**
     * PHP extensions
     * @var array
     */
    protected array $extensions = [];

    /**
     * PHP disabled classes
     * @var array
     */
    protected array $disabledFunctions = [];

    /**
     * PHP disabled classes
     * @var array
     */
    protected array $disabledClasses = [];

    /**
     * PHP error reporting constants
     * @var array
     */
    protected array $errorReporting = [
        E_ALL               => 'E_ALL',
        E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        E_DEPRECATED        => 'E_DEPRECATED',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_STRICT            => 'E_STRICT',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_CORE_ERROR        => 'E_CORE_ERROR',
        E_NOTICE            => 'E_NOTICE',
        E_PARSE             => 'E_PARSE',
        E_WARNING           => 'E_WARNING',
        E_ERROR             => 'E_ERROR',
    ];

    /**
     * Constructor
     *
     * Instantiate a PHP handler object
     *
     * @param  ?string $name
     * @param  ?Logger $logger
     * @param  array   $loggingParams
     */
    public function __construct(?string $name = null, ?Logger $logger = null, array $loggingParams = [])
    {
        parent::__construct($name, $logger, $loggingParams);

        $this->parseErrorSettings();
        $this->parseLimits();
        $this->parseDisabled();

        $this->dateTime   = ini_get('date.timezone');
        $this->extensions = array_map('strtolower', get_loaded_extensions());

        sort($this->extensions);
    }

    /**
     * Get PHP INI setting
     *
     * @param  string $setting
     * @return mixed
     */
    public function getIniSetting(string $setting): mixed
    {
        return ini_get($setting);
    }

    /**
     * Get full PHP version
     *
     * @param  bool $withExtra
     * @return string
     */
    public function getPhpVersion(bool $withExtra = true): string
    {
        return (!$withExtra) ? str_replace($this->extraVersion, '', $this->version) : $this->version;
    }

    /**
     * Get PHP major version
     *
     * @return int
     */
    public function getPhpMajorVersion(): int
    {
        return $this->majorVersion;
    }

    /**
     * Get PHP minor version
     *
     * @return int
     */
    public function getPhpMinorVersion(): int
    {
        return $this->minorVersion;
    }

    /**
     * Get PHP release version
     *
     * @return int
     */
    public function getPhpReleaseVersion(): int
    {
        return $this->releaseVersion;
    }

    /**
     * Get PHP extra version
     *
     * @return string
     */
    public function getPhpExtraVersion(): string
    {
        return $this->extraVersion;
    }

    /**
     * Get date/time
     *
     * @return ?string
     */
    public function getDateTime(): ?string
    {
        return $this->dateTime;
    }

    /**
     * Get error settings
     *
     * @return array
     */
    public function getErrorSettings(): array
    {
        return $this->errorSettings;
    }

    /**
     * Get error setting
     *
     * @param  string $setting
     * @return mixed
     */
    public function getErrorSetting(string $setting): mixed
    {
        return $this->errorSettings[$setting] ?? null;
    }

    /**
     * Get human-readable error reporting list
     *
     * @return array
     */
    public function getErrorReportingList(): array
    {
        return $this->errorSettings['error_reporting_list'];
    }

    /**
     * Has error level
     *
     * @param  int $level
     * @return bool
     */
    public function hasErrorLevel(int $level): bool
    {
        return in_array($this->errorReporting[$level], $this->errorSettings['error_reporting_list']);
    }

    /**
     * Get limits
     *
     * @return array
     */
    public function getLimits(): array
    {
        return $this->limits;
    }

    /**
     * Get limit
     *
     * @param  string $limit
     * @return mixed
     */
    public function getLimit(string $limit): mixed
    {
        return $this->limits[$limit] ?? null;
    }

    /**
     * Get extensions
     *
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Has extensions
     *
     * @param  string $extension
     * @return bool
     */
    public function hasExtension(string $extension): bool
    {
        return in_array(strtolower($extension), $this->extensions);
    }

    /**
     * Get disabled functions
     *
     * @return array
     */
    public function getDisabledFunctions(): array
    {
        return $this->disabledFunctions;
    }

    /**
     * Has disabled function
     *
     * @param  string $function
     * @return bool
     */
    public function hasDisabledFunction(string $function): bool
    {
        return in_array($function, $this->disabledFunctions);
    }

    /**
     * Get disabled classes
     *
     * @return array
     */
    public function getDisabledClasses(): array
    {
        return $this->disabledClasses;
    }

    /**
     * Has disabled class
     *
     * @param  string $class
     * @return bool
     */
    public function hasDisabledClass(string $class): bool
    {
        return in_array($class, $this->disabledClasses);
    }

    /**
     * Prepare handler data for storage
     *
     * @return array
     */
    public function prepare(): array
    {
        return [
            'php_version'         => $this->version,
            'php_major_version'   => $this->majorVersion,
            'php_minor_version'   => $this->minorVersion,
            'php_release_version' => $this->releaseVersion,
            'php_extra_version'   => $this->extraVersion,
            'data_time'           => $this->dateTime,
            'error_settings'      => $this->errorSettings,
            'limits'              => $this->limits,
            'extensions'          => $this->extensions,
            'disabled_functions'  => $this->disabledFunctions,
            'disabled_classes'    => $this->disabledClasses,
        ];
    }

    /**
     * Prepare header string
     *
     * @return string
     */
    public function prepareHeaderAsString(): string
    {
        $string  = ((!empty($this->name)) ? $this->name . ' ' : '') . 'PHP Handler';
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
        $string  = 'PHP Version: ' . $this->version . PHP_EOL . str_repeat('-', strlen($this->version) + 13) . PHP_EOL;
        $string .= ' - Major Version: ' . $this->majorVersion . PHP_EOL;
        $string .= ' - Minor Version: ' . $this->minorVersion . PHP_EOL;
        $string .= ' - Release Version: ' . $this->releaseVersion . PHP_EOL;
        $string .= ' - Extra Version: ' . $this->extraVersion . PHP_EOL . PHP_EOL;

        $string .= 'Date/Time:' . PHP_EOL . str_repeat('-', 10) . PHP_EOL;
        $string .= ' - ' . $this->dateTime . PHP_EOL . PHP_EOL;

        $string .= 'Error Settings:' . PHP_EOL . str_repeat('-', 15) . PHP_EOL;
        $string .= ' - Display Errors: ' . (($this->errorSettings['display_errors']) ? 'On' : 'Off') . PHP_EOL;
        $string .= ' - Display Startup Errors: ' . (($this->errorSettings['display_startup_errors']) ? 'On' : 'Off') . PHP_EOL;
        $string .= ' - Log Errors: ' . (($this->errorSettings['log_errors']) ? 'On' : 'Off') . PHP_EOL;
        $string .= ' - Error Reporting: ' . $this->errorSettings['error_reporting'] . PHP_EOL;
        $string .= ' - Error Reporting List: ' . implode(', ', $this->errorSettings['error_reporting_list']) . PHP_EOL . PHP_EOL;

        $string .= 'Limits:' . PHP_EOL . str_repeat('-', 7) . PHP_EOL;
        $string .= ' - Max Execution Time: ' . $this->limits['max_execution_time'] . PHP_EOL;
        $string .= ' - Max Input Time: ' . $this->limits['max_input_time'] . PHP_EOL;
        $string .= ' - Max Input Nesting Level: ' . $this->limits['max_input_nesting_level'] . PHP_EOL;
        $string .= ' - Max Input Vars: ' . $this->limits['max_input_vars'] . PHP_EOL;
        $string .= ' - Post Max Size: ' . $this->limits['post_max_size'] . PHP_EOL;
        $string .= ' - File Uploads: ' . (($this->limits['file_uploads']) ? 'On' : 'Off') . PHP_EOL;
        $string .= ' - Upload Max Filesize: ' . $this->limits['upload_max_filesize'] . PHP_EOL;
        $string .= ' - Max File Uploads: ' . $this->limits['max_file_uploads'] . PHP_EOL . PHP_EOL;

        $string .= 'Disabled Functions:' . PHP_EOL . str_repeat('-', 19) . PHP_EOL;
        $string .= ' - ' . (!empty($this->disabledFunctions) ? implode(', ', $this->disabledFunctions) : '(None)') . PHP_EOL . PHP_EOL;

        $string .= 'Disabled Classes:' . PHP_EOL . str_repeat('-', 17) . PHP_EOL;
        $string .= ' - ' . (!empty($this->disabledClasses) ? implode(', ', $this->disabledClasses) : '(None)') . PHP_EOL . PHP_EOL;

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
            $logLevel     = $this->loggingParams['level'] ?? null;
            $versionLimit = $this->loggingParams['version'] ?? null;
            $extensions   = $this->loggingParams['extensions'] ?? null;
            $useContext   = $this->loggingParams['context'] ?? null;

            if ($logLevel !== null) {
                $context = [];

                if (!empty($versionLimit) || !empty($extensions)) {
                    if (!empty($versionLimit)) {
                        if (version_compare($this->getPhpVersion(false), $versionLimit) == -1) {
                            $this->logger->log($logLevel, 'The current version of PHP ' . $this->getPhpVersion(false) .
                                ' is less than the required version ' . $versionLimit . '.',
                                $context
                            );
                        }
                    }
                    if (!empty($extensions)) {
                        if (is_string($extensions)) {
                            if (str_contains($extensions, ',')) {
                                $extensions = array_map(function ($value) {
                                    return strtolower(trim($value));
                                }, explode(',', $extensions));
                            } else {
                                $extensions = [strtolower($extensions)];
                            }
                        }
                        $extensionDiff = array_diff($extensions, $this->extensions);
                        if (!empty($extensionDiff)) {
                            $this->logger->log($logLevel, 'The current of PHP extensions are required but not active: ' .
                                implode(', ', $extensionDiff),
                                $context
                            );
                        }
                    }
                } else {
                    $context['php_info'] = (($useContext !== null) && (strtolower($useContext) == 'text')) ?
                        $this->prepareAsString() : $this->prepare();

                    if (is_string($useContext)) {
                        $context['format'] = $useContext;
                    }

                    $this->logger->log($logLevel, "PHP info has been logged", $context);
                }
            } else {
                throw new Exception('Error: The log level parameter was not set.');
            }
        }
    }

    /**
     * Get PHP error settings
     *
     * @return void
     */
    public function parseErrorSettings(): void
    {
        $this->errorSettings['error_reporting']        = ini_get('error_reporting');
        $this->errorSettings['display_errors']         = !empty(ini_get('display_errors'));
        $this->errorSettings['display_startup_errors'] = !empty(ini_get('display_startup_errors'));
        $this->errorSettings['log_errors']             = !empty(ini_get('log_errors'));

        if (!empty($this->errorSettings['error_reporting'])) {
            if ($this->errorSettings['error_reporting'] == E_ALL) {
                $this->errorSettings['error_reporting_list'][] = $this->errorReporting[E_ALL];
            } else {
                foreach($this->errorReporting as $errorNumber => $errorName) {
                    if (($this->errorSettings['error_reporting'] & $errorNumber) == $errorNumber) {
                        $this->errorSettings['error_reporting_list'][] = $errorName;
                    }
                }
            }
        }
    }

    /**
     * Get PHP limits
     *
     * @return void
     */
    public function parseLimits(): void
    {
        $this->limits['max_execution_time']      = ini_get('max_execution_time');
        $this->limits['max_input_time']          = ini_get('max_input_time');
        $this->limits['max_input_nesting_level'] = ini_get('max_input_nesting_level');
        $this->limits['max_input_vars']          = ini_get('max_input_vars');
        $this->limits['post_max_size']           = ini_get('post_max_size');
        $this->limits['file_uploads']            = ini_get('file_uploads');
        $this->limits['upload_max_filesize']     = ini_get('upload_max_filesize');
        $this->limits['max_file_uploads']        = ini_get('max_file_uploads');
    }

    /**
     * Get PHP disabled functions and classes
     *
     * @return void
     */
    public function parseDisabled(): void
    {
        $disabledFunctions = ini_get('disable_functions');
        $disabledClasses   = ini_get('disable_classes');

        if (!empty($disabledFunctions)) {
            $this->disabledFunctions = array_map('trim', explode(',', $disabledFunctions));
        }
        if (!empty($disabledClasses)) {
            $this->disabledClasses = array_map('trim', explode(',', $disabledClasses));
        }
    }

}
