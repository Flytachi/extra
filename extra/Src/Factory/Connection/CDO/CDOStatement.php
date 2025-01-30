<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\CDO;

use PDO;
use PDOStatement;

class CDOStatement
{
    private PDOStatement $stmt;
    private array $bindings = [];

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR): bool
    {
        $this->bindings[] = [$parameter, $value, $data_type];
        return $this->stmt->bindValue($parameter, $value, $data_type);
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function updateStm(PDOStatement $stmt): void
    {
        $this->stmt = $stmt;
        foreach ($this->bindings as $binding) {
            $this->stmt->bindValue(...$binding);
        }
    }

    public function getStmt(): PDOStatement
    {
        return $this->stmt;
    }
}
