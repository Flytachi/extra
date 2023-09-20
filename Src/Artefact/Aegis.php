<?php

namespace Extra\Src\Artefact;

use Extra\Src\Enum\HttpCode;

class Aegis
{
    private static array $shards = [];

    /**
     * @param string $keyName
     * @param Shard $shard
     */
    public static function raise(string $keyName, Shard $shard): void
    {
        if (array_key_exists($keyName, self::$shards))
            ArtefactError::throw(HttpCode::INTERNAL_SERVER_ERROR,"A shard with the key \"{$keyName}\" already exists!");
        self::$shards[$keyName] = $shard;
    }

    /**
     * @param string $keyName
     * @return Shard
     */
    public static function getShard(string $keyName): Shard
    {
        if (!array_key_exists($keyName, self::$shards))
            ArtefactError::throw(HttpCode::INTERNAL_SERVER_ERROR,"Shard with key \"{$keyName}\" not found!");
        return self::$shards[$keyName];
    }

}