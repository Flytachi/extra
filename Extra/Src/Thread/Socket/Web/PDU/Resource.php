<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Thread\Socket\Web\PDU;

class Resource
{
    private $connect;
    private array $store = [];

    /**
     * @param $connect
     */
    public function __construct($connect)
    {
        $this->connect = $connect;
    }

    public function getStore(): array
    {
        return $this->store;
    }

    public function setStore(array $store): void
    {
        $this->store = $store;
    }

    public function getConnect()
    {
        return $this->connect;
    }

    public function __toString(): string
    {
        return (string) $this->connect;
    }
}
