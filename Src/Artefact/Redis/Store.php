<?php

namespace Extra\Src\Artefact\Redis;

use Extra\Src\Artefact\Aegis;

/**
 * It is an abstract class for processing data storage operations in Redis.
 *
 * @version 1.1
 * @author Flytachi
 */
abstract class Store
{
    /** @var string $shardKey Aegis shard redis key (default => 'store') */
    protected static string $shardKey = 'store';
    protected static int $dbIndex = 0;

    protected static function init(?int $dbIndex = null): \Redis
    {
        if ($dbIndex !== null && $dbIndex != static::$dbIndex) {
            Aegis::store(static::$shardKey)->select($dbIndex);
            static::$dbIndex = $dbIndex;
        }
        return Aegis::store(static::$shardKey);
    }
}