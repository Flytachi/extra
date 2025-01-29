<?php

namespace Flytachi\Extra\Src\Factory;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\StreamHandler;

abstract class ExtraConfig
{
    public static AbstractProcessingHandler $loggerStreamHandler;
    public static string $pathRoot;
    public static string $pathEnv;
    public static string $pathApp;
    public static string $pathPublic;
    public static string $pathStorage;
    public static string $pathStorageCache;
    public static string $pathStorageLog;
    public static string $pathFileMapping;

    /**
     * @param string|null $pathRoot
     * @param string|null $pathApp
     * @param string|null $pathEnv
     * @param string|null $pathPublic
     * @param string|null $pathStorage
     * @param string|null $pathStorageCache
     * @param string|null $pathStorageLog
     * @param string|null $pathFileMapping
     * @param AbstractProcessingHandler|null $loggerStreamHandler
     * @return void
     */
    public static function init(
        ?string $pathRoot = null,
        ?string $pathApp = null,
        ?string $pathEnv = null,
        ?string $pathPublic = null,
        ?string $pathStorage = null,
        ?string $pathStorageCache = null,
        ?string $pathStorageLog = null,
        ?string $pathFileMapping = null,
        ?AbstractProcessingHandler $loggerStreamHandler = null,
    ): void {
        // root
        if ($pathRoot === null) {
            $pathRoot = dirname(__DIR__, 2);
        }

        // app
        if ($pathApp === null) {
            $pathApp = $pathRoot . '/app';
        }

        // env
        if ($pathEnv === null) {
            $pathEnv = $pathRoot . '/.env';
        }

        // public
        if ($pathPublic === null) {
            $pathPublic = $pathRoot . '/public';
        }

        // storage
        if ($pathStorage === null) {
            $pathStorage = $pathRoot . '/storage';
        }

        // storage cache
        if ($pathStorageCache === null) {
            $pathStorageCache = $pathStorage . '/cache';
        }

        // storage log
        if ($pathStorageLog === null) {
            $pathStorageLog = $pathStorage . '/logs';
        }

        // mapping
        if ($pathFileMapping === null) {
            $pathFileMapping = $pathStorageCache . '/mapping.php';
        }

        self::$pathRoot = $pathRoot;
        self::$pathApp = $pathApp;
        self::$pathEnv = $pathEnv;
        self::$pathPublic = $pathPublic;
        self::$pathStorage = $pathStorage;
        self::$pathStorageCache = $pathStorageCache;
        self::$pathStorageLog = $pathStorageLog;
        self::$pathFileMapping = $pathFileMapping;

        // logger stream handler
        if ($loggerStreamHandler === null) {
            self::$loggerStreamHandler = new StreamHandler($pathStorageLog . '/frame.log');
            self::$loggerStreamHandler->setFormatter(new LineFormatter(
                dateFormat: "Y-m-d H:i:s P",
                ignoreEmptyContextAndExtra: true
            ));
        }
    }
}
