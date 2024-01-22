<?php

namespace Extra\Src\Artefact\Mechanism;

use Extra\Src\Artefact\ArtefactError;
use Extra\Src\HttpCode;
use Redis;

class ShardRedis
{
    private ?Redis $store = null;
    private string $host;
    private int $port;
    private ?string $password;
    private int $databaseIndex;

    /**
     * @param string $host
     * @param int $port
     * @param string|null $password
     * @param int $databaseIndex
     */
    public function __construct(string $host, int $port, ?string $password = null, int $databaseIndex = 0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->databaseIndex = $databaseIndex;
    }

    public function getStore(): ?Redis
    {
        return $this->store;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getDatabaseIndex(): int
    {
        return $this->databaseIndex;
    }

    public final function connect(): void
    {
        if (is_null($this->store)) {
            try {
                $this->store  = new \Redis();
                $this->store->connect($this->host, $this->port, 10);
                if($this->password) $this->store->auth($this->password);
                $this->store->select($this->databaseIndex);
            } catch (\Exception $exception) {
                ArtefactError::throw(HttpCode::INTERNAL_SERVER_ERROR, $exception->getMessage());
            }
        }
    }

    /**
     * @return Redis
     */
    public function connection(): Redis
    {
        $this->connect();
        return $this->store;
    }
}