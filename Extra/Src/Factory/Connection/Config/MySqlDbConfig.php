<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Config;

use Flytachi\Extra\Src\Factory\Connection\Config\Common\BaseDbConfig;

abstract class MySqlDbConfig extends BaseDbConfig
{
    protected string $host = 'localhost';
    protected int $port = 3306;
    protected string $database;
    protected string $username = 'root';
    protected string $password = '';
    protected ?string $charset = null;

    public function getDns(): string
    {
        $dns = parent::getDns();
        if ($this->charset !== null) {
            $dns .= 'charset=' . $this->charset . ';';
        }
        return $dns;
    }

    final public function getDriver(): string
    {
        return 'mysql';
    }
}
