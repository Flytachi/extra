<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Config\Common;

use Flytachi\Extra\Src\Factory\Connection\CDO\CDO;

interface DbConfigInterface
{
    public function sepUp(): void;
    public function getDns(): string;
    public function getPersistentStatus(): bool;
    public function getDriver(): string;
    public function getUsername(): string;
    public function getPassword(): string;
    public function connect(): void;
    public function disconnect(): void;
    public function reconnect(): void;
    public function connection(): CDO;
    public function getSchema(): ?string;
}
