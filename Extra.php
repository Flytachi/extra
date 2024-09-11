<?php

namespace Extra;

use DirectoryIterator;
use Extra\Src\Error\BaseError;
use Extra\Src\Error\Error;
use Extra\Src\HttpCode;

/**
 * Class Extra
 *
 * `Extra` is a helper class to manage application-level tasks such as autoload, initialization and configurations loading.
 *
 * The methods provided by `Extra` include:
 *
 * - `autoload(): void`: Handles automatic class file loading based on namespaces.
 * - `init(bool $isConsole = false): void`: Initializes the application, defines constants, loads functions, and checks directory write access.
 * - `warningHandler($severity, $message, $file, $line): void`: Error handler for managing PHP warnings.
 * - `loadFunction(): void`: Loads all available functions from the Function directory.
 *
 * @version 5.0
 * @author Flytachi
 */
class Extra
{
    /**
     * Registers an autoloader function and loads the specified class file when needed.
     *
     * @return void
     */
    public final static function autoload(): void
    {
        spl_autoload_register(function($class) {
            $file = PATH_APP . '/' . strtr($class, '\\', '/') . '.php';
            if (file_exists($file)) require $file;
        });
        // Env
        if (is_readable(ENV_PATH)) {
            $lines = file(ENV_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }

    /**
     * Initializes the application.
     *
     * @param bool $isConsole Whether the application is running in a console environment. Default is false.
     * @return void
     */
    public final static function init(bool $isConsole = false): void
    {
        define('EXTRA_STARTUP_TIME', microtime(true));
        require dirname(__DIR__) . '/defines.php';
        self::loadFunction();
        self::autoload();

        try {
            self::loadConfig();

            if (!$isConsole) {
                if (!is_writable(PATH_STORAGE))
                    BaseError::throw(HttpCode::NOT_IMPLEMENTED, "The \"storage\" folder does not have write access");
                if (!is_writable(PATH_LOG))
                    BaseError::throw(HttpCode::NOT_IMPLEMENTED, "The \"storage/logs\" folder does not have write access");
                if (!is_writable(PATH_CACHE))
                    BaseError::throw(HttpCode::NOT_IMPLEMENTED, "The \"storage/cache\" folder does not have write access");
            }
        } catch (\Throwable $exception) {
            BaseError::throw(HttpCode::NOT_IMPLEMENTED,
                $exception->getMessage() . ' in ' . $exception->getFile() . '(' . $exception->getLine() . ')'
            );
        }

        define('SERVER_SCHEME', ($_SERVER['REQUEST_SCHEME'] ?? 'http') . "://" . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        date_default_timezone_set(env('TIME_ZONE', 'UTC'));

        if (env('DEBUG', false)) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            set_error_handler('\Extra\Extra::warningHandler');
        }

        // composer
        if (COMPOSER_LOADING && file_exists(PATH_ROOT . '/vendor/autoload.php'))
            include PATH_ROOT . '/vendor/autoload.php';
    }

    /**
     * Handles warnings and throws an error with HTTP code 500 (Internal Server Error).
     *
     * @param int $severity The severity level of the warning.
     * @param string $message The warning message.
     * @param string $file The file in which the warning occurred.
     * @param int $line The line number at which the warning occurred.
     * @return void
     */
    public final static function warningHandler($severity, $message, $file, $line): void
    {
        if (!(error_reporting() & $severity)) return;
        Error::throw(HttpCode::INTERNAL_SERVER_ERROR,
            "Warning: $message in $file on line $line"
        );
    }

    /**
     * Loads all PHP files in the directory PATH_APP/Extra/Function.
     *
     * @return void
     */
    public final static function loadFunction(): void
    {
        $directory = new DirectoryIterator(PATH_APP . '/Extra/Function');
        foreach ($directory as $fileInfo) {
            if ($fileInfo->isDot()) continue;
            if ($fileInfo->getExtension() === 'php') require $fileInfo->getPathname();
        }
    }

    /**
     * Loads configuration files from the "Config" directory.
     *
     * @return void
     */
    public final static function loadConfig(): void
    {
        $directory = new DirectoryIterator(PATH_APP . '/Config');
        foreach ($directory as $fileInfo) {
            if ($fileInfo->isDot()) continue;
            if ($fileInfo->getExtension() === 'php') require $fileInfo->getPathname();
        }
    }

}
