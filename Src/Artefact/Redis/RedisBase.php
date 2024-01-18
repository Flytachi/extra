<?php

namespace Extra\Src\Artefact\Redis;

use Extra\Src\HttpCode;

class RedisBase
{
    private static string $host;
    private static int $port = 6379;
    private static ?string $pass = null;
    private static int $dbIndex = 0;
    private static null|\Redis $storage = null;

    private static function init(): void
    {
        if (self::$storage == null) {
            try {
                self::$storage  = new \Redis();
                self::$storage->connect(env('REDIS_MAIN_HOST'), env('REDIS_MAIN_PORT'), 10);
                self::$storage->auth(env('REDIS_MAIN_PASS'));
                self::$storage->select(env('REDIS_MAIN_DBNAME', 0));
            } catch (\Exception $exception) {
                RedisError::throw(HttpCode::INTERNAL_SERVER_ERROR, $exception->getMessage());
            }
        }
    }
}