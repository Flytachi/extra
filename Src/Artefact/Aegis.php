<?php

namespace Extra\Src\Artefact;

use Extra\Src\Artefact\CDO\CDO;
use Extra\Src\HttpCode;

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

    public final static function connectByKey(string $keyName): void
    {
        self::getShard($keyName)->connect();
    }

    public final static function db(?string $keyName = null): CDO
    {
        $shard = ($keyName) ? self::getShard($keyName) : reset(self::$shards);
        return $shard->connection();
    }

}