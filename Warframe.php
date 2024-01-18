<?php

namespace Extra;

use Extra\Src\Error\BaseError;
use Extra\Src\Error\Error;
use Extra\Src\HttpCode;

/**
 * Class Warframe
 *
 * `Warframe` is a helper class to manage application-level tasks such as autoload, initialization and configurations loading.
 *
 * The methods provided by `Warframe` include:
 *
 * - `autoload(): void`: Handles automatic class file loading based on namespaces.
 * - `init(bool $isConsole = false): void`: Initializes the application, defines constants, loads functions, and checks directory write access.
 * - `warningHandler($severity, $message, $file, $line): void`: Error handler for managing PHP warnings.
 * - `loadFunction(): void`: Loads all available functions from the Function directory.
 *
 * @version 4.0
 * @author Flytachi
 */
class Warframe
{
    /**
     * Registers an autoloader function and loads the specified class file when needed.
     *
     * @return void
     */
    public final static function autoload(): void
    {
        spl_autoload_register(function($class) {
            $file = PATH_APP . '/' . str_replace("\\", '/', $class) . '.php';
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
     * @throws BaseError
     */
    public final static function init(bool $isConsole = false): void
    {
        define('WARFRAME_STARTUP_TIME', microtime(true));
        require dirname(__DIR__) . '/defines.php';
        self::loadFunction();
        self::autoload();

        try {
            foreach (glob(dirname(__DIR__)."/Config/*") as $function) require $function;

            if (!$isConsole) {
                if (!is_writable(PATH_STORAGE))
                    BaseError::throw(HttpCode::INTERNAL_SERVER_ERROR, "The \"storage\" folder does not have write access");
                if (!is_writable(PATH_LOG))
                    BaseError::throw(HttpCode::INTERNAL_SERVER_ERROR, "The \"storage/logs\" folder does not have write access");
                if (!is_writable(PATH_CACHE))
                    BaseError::throw(HttpCode::INTERNAL_SERVER_ERROR, "The \"storage/cache\" folder does not have write access");
            }
        } catch (\Throwable $exception) {
            BaseError::throw(HttpCode::INTERNAL_SERVER_ERROR,
                $exception->getMessage() . ' in ' . $exception->getFile() . '(' . $exception->getLine() . ')'
            );
        }

        define('SERVER_SCHEME', ($_SERVER['REQUEST_SCHEME'] ?? 'http') . "://" . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        date_default_timezone_set(env('TIME_ZONE', 'UTC'));

        if (env('DEBUG', false)) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            set_error_handler('\Extra\Warframe::warningHandler');
        }
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
     * Loads all function files from the "Function" directory.
     *
     * This method scans the "Function" directory and includes all PHP files found.
     *
     * @return void
     */
    public final static function loadFunction(): void
    {
        foreach (glob(dirname(__FILE__)."/Function/*") as $function) require $function;
    }

}
