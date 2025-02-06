<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection;

use Flytachi\Extra\Src\Factory\Connection\CDO\CDO;
use Flytachi\Extra\Src\Factory\Connection\Config\Common\DbConfigInterface;
use Flytachi\Extra\Src\Factory\Connection\Config\Common\RedisConfigInterface;
use Redis;

abstract class ConnectionPool
{
    /* @var array<string, DbConfigInterface> */
    private static array $dbConfig = [];

    /* @var array<string, RedisConfigInterface> */
    private static array $redisConfig = [];

    /**
     * @param string $className
     * @return DbConfigInterface
     */
    public static function getConfigDb(string $className): DbConfigInterface
    {
        $key = base64_encode($className);
        if (!array_key_exists($key, self::$dbConfig)) {
            /** @var DbConfigInterface $newDbConfig */
            $newDbConfig = new $className();
            $newDbConfig->sepUp();
            self::$dbConfig[$key] = $newDbConfig;
        }
        return self::$dbConfig[$key];
    }

    /**
     * @param string $className
     * @return CDO
     */
    final public static function db(string $className): CDO
    {
        $config = self::getConfigDb($className);
        return $config->connection();
    }

    /**
     * @return DbConfigInterface[]
     */
    public static function showDbConfigs(): array
    {
        return self::$dbConfig;
    }

    /**
     * @param string $className
     * @return RedisConfigInterface
     */
    public static function getConfigRedis(string $className): RedisConfigInterface
    {
        $key = base64_encode($className);
        if (!array_key_exists($key, self::$redisConfig)) {
            /** @var RedisConfigInterface $newDbConfig */
            $newDbConfig = new $className();
            $newDbConfig->sepUp();
            self::$redisConfig[$key] = $newDbConfig;
        }
        return self::$redisConfig[$key];
    }

    /**
     * @param string $className
     * @return Redis
     */
    final public static function store(string $className): Redis
    {
        $config = self::getConfigRedis($className);
        return $config->connection();
    }

    /**
     * @return RedisConfigInterface[]
     */
    public static function showRedisConfigs(): array
    {
        return self::$redisConfig;
    }
}
