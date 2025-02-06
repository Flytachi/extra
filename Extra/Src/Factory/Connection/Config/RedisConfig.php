<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Config;

use Flytachi\Extra\Src\Factory\Connection\Config\Common\BaseRedisConfig;

abstract class RedisConfig extends BaseRedisConfig
{
    protected string $host = 'localhost';
    protected int $port = 6379;
    protected string $password = '';
    protected int $databaseIndex = 0;
}
