<?php

namespace Extra\Src\Artefact\Redis;

use Extra\Src\Artefact\Aegis;

/**
 * It is an abstract class for processing data storage operations in Redis.
 *
 * @version 1.0
 * @author Flytachi
 */
abstract class Store
{
    /** @var string $shardKey Aegis shard redis key (default => 'store') */
    protected static string $shardKey = 'store';

    protected static function init(?int $dbIndex = null): \Redis
    {
        if ($dbIndex !== null) Aegis::store(self::$shardKey)->select($dbIndex);
        return Aegis::store(self::$shardKey);
    }
}