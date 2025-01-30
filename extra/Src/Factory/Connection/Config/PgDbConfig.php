<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Config;

use Flytachi\Extra\Src\Factory\Connection\Config\Common\BaseDbConfig;

abstract class PgDbConfig extends BaseDbConfig
{
    protected string $host = 'localhost';
    protected int $port = 5432;
    protected string $database = 'postgres';
    protected string $username = 'postgres';
    protected string $password = '';
    protected string $schema = 'public';
    protected ?string $charset = null;

    public function getDns(): string
    {
        $dns = parent::getDns();
        if ($this->charset !== null) {
            $dns .= "options='--client_encoding=" . $this->charset . "';";
        }
        return $dns;
    }

    final public function getDriver(): string
    {
        return 'pgsql';
    }
}
