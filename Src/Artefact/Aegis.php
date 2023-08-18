<?php

namespace Extra\Src\Artefact;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Route;
use MongoDB\Driver\Exception\UnexpectedValueException;

class Aegis
{
    private static array $shards;

    public static function raise(string $keyName, Shard $shard): void
    {
        self::$shards[$keyName] = $shard;
    }

    public static function getShard(string $keyName): Shard
    {
        if (!array_key_exists($keyName, self::$shards))
            Route::Throwable(HttpCode::INTERNAL_SERVER_ERROR, "Shard with key \"{$keyName}\" not found!");
        return self::$shards[$keyName];
    }

}