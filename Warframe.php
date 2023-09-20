<?php

namespace Extra;

use Extra\Src\Artefact\Shard;
use Extra\Src\CDO\CDO;
use Extra\Src\Enum\HttpCode;
use Extra\Src\Error\Error;
use Extra\Src\Route\Route;
use Predis\Response\ErrorInterface;

class Warframe
{
    private static array $dbs = [];

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
     * @throws Error
     */
    public final static function init(): void
    {
        require dirname(__DIR__) . '/defines.php';
        self::loadFunction();
        self::autoload();

        try {
            foreach (glob(dirname(__DIR__)."/Config/*") as $function) require $function;

            if (!is_dir(PATH_LOG)) mkdir(PATH_LOG);
            if (!is_writable(PATH_LOG))
                Error::throw(HttpCode::INTERNAL_SERVER_ERROR, "The \"storage\" folder does not have write access");
        } catch (\Throwable $err) {
            Error::throw(HttpCode::INTERNAL_SERVER_ERROR,
                $err->getMessage() . ' in ' . $err->getFile() . '(' . $err->getLine() . ')'
            );
        }

        define('SERVER_SCHEME', ($_SERVER['REQUEST_SCHEME'] ?? 'http') . "://" . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        date_default_timezone_set(env('TIME_ZONE', 'UTC'));

        if (env('DEBUG', false)) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        }
    }

    public final static function loadFunction(): void
    {
        foreach (glob(dirname(__FILE__)."/Function/*") as $function) require $function;
    }

    public final static function setDb(string $key, Shard $shard): void
    {
        if (!array_key_exists($key, self::$dbs)) {
            self::$dbs[$key] = new CDO($shard, env('DEBUG', false));
        }
    }

    public final static function db(?string $key = null): CDO
    {
        return ($key) ? self::$dbs[$key] : reset(self::$dbs);
    }

}
