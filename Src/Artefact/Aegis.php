<?php

namespace Extra\Src\Artefact;

use Extra\Src\Artefact\CDO\CDO;
use Extra\Src\Artefact\Mechanism\Shard;
use Extra\Src\Artefact\Mechanism\ShardRedis;
use Extra\Src\HttpCode;
use Redis;

/**
 * Class Aegis
 *
 * `Aegis` is a utility class that manages multiple `Shard` instances,
 * allowing you to connect to different databases within the same application.
 *
 * This class has a static property `$shards` which stores the numerous instances
 * of `Shard`, each identified by a unique keyname. The `raise()` method is used
 * to add shards to this property.
 *
 * The `getShard()` method allows you to fetch a `Shard` by its keyname.
 * `connectByKey()` enables you to establish a connection with a
 * specific shard.
 *
 * `db()` method returns the instance of `CDO` representing the connection to
 * the database for a given keyname or the first shard in the list if the keyname
 * is not provided.
 *
 * @version 3.2
 * @author Flytachi
 */
abstract class Aegis
{
    /* @var array<string, Shard> */
    private static array $shards = [];
    /* @var array<string, ShardRedis> */
    private static array $shardRedis = [];

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

    public final static function db(?string $keyName = null): CDO
    {
        $shard = ($keyName) ? self::getShard($keyName) : reset(self::$shards);
        return $shard->connection();
    }


    /**
     * @param string $keyName
     * @param ShardRedis $shard
     */
    public static function raiseRedis(string $keyName, ShardRedis $shard): void
    {
        if (array_key_exists($keyName, self::$shardRedis))
            ArtefactError::throw(HttpCode::INTERNAL_SERVER_ERROR,"A shard redis with the key \"{$keyName}\" already exists!");
        self::$shardRedis[$keyName] = $shard;
    }

    /**
     * @param string $keyName
     * @return ShardRedis
     */
    public static function getShardRedis(string $keyName): ShardRedis
    {
        if (!array_key_exists($keyName, self::$shardRedis))
            ArtefactError::throw(HttpCode::INTERNAL_SERVER_ERROR,"ShardRedis with key \"{$keyName}\" not found!");
        return self::$shardRedis[$keyName];
    }

    public final static function store(?string $keyName = null): Redis
    {
        $shard = ($keyName) ? self::getShardRedis($keyName) : reset(self::$shardRedis);
        return $shard->connection();
    }

}