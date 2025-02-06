<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Config\Common;

use Redis;

abstract class BaseRedisConfig implements RedisConfigInterface
{
    private ?Redis $store = null;

    final public function connect(): void
    {
        if (is_null($this->store)) {
            $this->store = new Redis();
            $this->store->connect($this->host, $this->port, 10);
            if ($this->password) {
                $this->store->auth($this->password);
            }
            $this->store->select($this->databaseIndex);
        }
    }

    final public function disconnect(): void
    {
        $this->store->close();
        $this->store = null;
    }

    final public function reconnect(): void
    {
        $this->disconnect();
        $this->connect();
    }

    /**
     * @return Redis
     */
    final public function connection(): Redis
    {
        $this->connect();
        return $this->store;
    }
}
