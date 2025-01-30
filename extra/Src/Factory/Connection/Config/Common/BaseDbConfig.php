<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Config\Common;

use Flytachi\Extra\Src\Factory\Connection\CDO\CDO;
use Flytachi\Extra\Src\Factory\Connection\CDO\CDOException;

abstract class BaseDbConfig implements DbConfigInterface
{
    private ?CDO $cdo = null;
    protected bool $isPersistent = false;

    public function getDns(): string
    {
        return $this->getDriver()
            . ':host=' . $this->host
            . ';port=' . $this->port
            . ';dbname=' . $this->database
            . ';';
    }

    final public function getUsername(): string
    {
        return $this->username;
    }

    final public function getPassword(): string
    {
        return $this->password;
    }

    final public function getPersistentStatus(): bool
    {
        return $this->isPersistent;
    }

    /**
     * @throws CDOException
     */
    final public function connect(): void
    {
        if (is_null($this->cdo)) {
            $this->cdo = new CDO($this, env('DEBUG', false));
        }
    }

    final public function disconnect(): void
    {
        $this->cdo = null;
    }

    /**
     * @throws CDOException
     */
    final public function reconnect(): void
    {
        $this->disconnect();
        $this->connect();
    }

    /**
     * @return CDO
     * @throws CDOException
     */
    final public function connection(): CDO
    {
        $this->connect();
        return $this->cdo;
    }
}
