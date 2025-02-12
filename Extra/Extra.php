<?php

declare(strict_types=1);

namespace Flytachi\Extra;

use Dotenv\Dotenv;
use Flytachi\Extra\Src\Factory\ExtraConfig;
use Flytachi\Extra\Src\Log\ExtraLogger;
use Psr\Log\LoggerInterface;

/**
 * Class Extra
 *
 * @version 1.0
 * @author Flytachi
 */
final class Extra extends ExtraConfig
{
    public static LoggerInterface $logger;

    public static function init(
        ?string $pathRoot = null,
        ?string $pathApp = null,
        ?string $pathEnv = null,
        ?string $pathPublic = null,
        ?string $pathResource = null,
        ?string $pathStorage = null,
        ?string $pathStorageCache = null,
        ?string $pathStorageLog = null,
        ?string $pathFileMapping = null,
        ?LoggerInterface $logger = null
    ): void {
        define('EXTRA_STARTUP_TIME', microtime(true));
        parent::init(
            $pathRoot,
            $pathApp,
            $pathEnv,
            $pathPublic,
            $pathResource,
            $pathStorage,
            $pathStorageCache,
            $pathStorageLog,
            $pathFileMapping
        );

        Dotenv::createImmutable(self::$pathRoot)
            ->safeLoad();

        define('SERVER_SCHEME', (
                $_SERVER['REQUEST_SCHEME'] ?? 'http') . "://" . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        date_default_timezone_set(env('TIME_ZONE', 'UTC'));

        if (env('DEBUG', false)) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        }

        // logger
        if ($logger === null) {
            self::$logger = new ExtraLogger(static::class);
        } else {
            self::$logger = $logger;
        }
    }
}
