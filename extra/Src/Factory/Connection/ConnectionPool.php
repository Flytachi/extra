<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection;

use Flytachi\Extra\Src\Factory\Connection\CDO\CDO;
use Flytachi\Extra\Src\Factory\Connection\Config\Common\DbConfigInterface;

abstract class ConnectionPool
{
    /* @var array<string, DbConfigInterface> */
    private static array $dbConfig = [];

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
}
