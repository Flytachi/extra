<?php

declare(strict_types=1);

namespace Flytachi\Extra;

use Dotenv\Dotenv;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\StreamHandler;

/**
 * Class Extra
 *
 * @version 1.0
 * @author Flytachi
 */
class Extra
{
    private static AbstractProcessingHandler $loggerStreamHandler;
    private static string $pathRoot;
    private static string $pathApp;
    private static string $pathPublic;
    private static string $pathStorage;

    /**
     * @param string|null $pathRoot
     * @param string|null $pathApp
     * @param string|null $pathPublic
     * @param string|null $pathStorage
     * @return void
     */
    public static function init(
        ?string $pathRoot = null,
        ?string $pathApp = null,
        ?string $pathPublic = null,
        ?string $pathStorage = null
    ): void {
        define('EXTRA_STARTUP_TIME', microtime(true));

        // Path Root
        self::setPaths($pathRoot, $pathApp, $pathPublic, $pathStorage);

        Dotenv::createImmutable(Extra::pathRoot())->load();

        define('SERVER_SCHEME', (
            $_SERVER['REQUEST_SCHEME'] ?? 'http') . "://" . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        date_default_timezone_set(env('TIME_ZONE', 'UTC'));

        if (env('DEBUG', false)) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        }
    }

    private static function setPaths(
        ?string $root = null,
        ?string $app = null,
        ?string $public = null,
        ?string $storage = null
    ): void {
        // root
        if ($root === null) {
            $root = dirname(__DIR__);
        }
        self::$pathRoot = $root;

        // app
        if ($app === null) {
            $app = $root . '/app';
        }
        self::$pathRoot = $root;

        // public
        if ($public === null) {
            $public = $root . '/public';
        }

        // public
        if ($storage === null) {
            $storage = $root . '/storage';
        }

        self::$pathRoot = $root;
        self::$pathApp = $app;
        self::$pathPublic = $public;
        self::$pathStorage = $storage;
    }

    public static function pathRoot(): string
    {
        return self::$pathRoot;
    }

    public static function pathApp(): string
    {
        return self::$pathApp;
    }

    public static function pathPublic(): string
    {
        return self::$pathPublic;
    }

    public static function pathStorage(): string
    {
        return self::$pathStorage;
    }

    public static function getLoggerStreamHandler(): AbstractProcessingHandler
    {
        if (!isset(self::$loggerStreamHandler)) {
            self::$loggerStreamHandler = new StreamHandler(self::pathStorage() . '/logs/frame.log');
            self::$loggerStreamHandler->setFormatter(new LineFormatter(
                dateFormat: "Y-m-d H:i:s P",
                ignoreEmptyContextAndExtra: true
            ));
        }
        return self::$loggerStreamHandler;
    }

    public static function setLoggerStreamHandler(AbstractProcessingHandler $loggerStreamHandler): void
    {
        self::$loggerStreamHandler = $loggerStreamHandler;
    }
}
