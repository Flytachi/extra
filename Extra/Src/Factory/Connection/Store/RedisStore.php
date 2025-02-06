<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Store;

use Flytachi\Extra\Src\Factory\Connection\ConnectionPool;

abstract class RedisStore
{
    /** @var class-string $redisConfigClassName redisConfig class name (default => RedisConfig::class) */
    protected static string $redisConfigClassName;
    protected static int $dbIndex = 0;

    protected static function init(?int $dbIndex = null): \Redis
    {
        if ($dbIndex !== null && $dbIndex != static::$dbIndex) {
            ConnectionPool::store(static::$redisConfigClassName)->select($dbIndex);
            static::$dbIndex = $dbIndex;
        }
        return ConnectionPool::store(static::$redisConfigClassName);
    }
}
