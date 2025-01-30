<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Config;

use Flytachi\Extra\Src\Factory\Connection\Config\Common\BaseDbConfig;

abstract class DbConfig extends BaseDbConfig
{
    protected string $driver;
    protected string $host;
    protected int $port;
    protected string $database;
    protected string $username;
    protected string $password;

    final public function getDriver(): string
    {
        return $this->driver;
    }
}
