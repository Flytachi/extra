<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Repository\Traits;

use Flytachi\Extra\Src\Factory\Connection\CDO\CDOException;
use Flytachi\Extra\Src\Factory\Connection\Qb;
use Flytachi\Extra\Src\Factory\Connection\Repository\RepositoryException;

trait RepositoryCrudTrait
{
    /**
     * @throws RepositoryException
     * @throws CDOException
     */
    public function insert(object|array $model): mixed
    {
        if ($this->isReadonly) {
            throw new RepositoryException('No write access (action insert)');
        }

        return $this->db()->insert(($this->schema ? $this->schema . '.' : '') . $this::$table, $model);
    }

    /**
     * @throws RepositoryException
     * @throws CDOException
     */
    public function insertGroup(object ...$models): void
    {
        if ($this->isReadonly) {
            throw new RepositoryException('No write access (action insert)');
        }

        $this->db()->insertGroup(($this->schema ? $this->schema . '.' : '') . $this::$table, ...$models);
    }

    /**
     * @throws RepositoryException
     * @throws CDOException
     */
    public function update(object|array $model, Qb $qb): int|string
    {
        if ($this->isReadonly) {
            throw new RepositoryException('No write access (action update)');
        }
        return $this->db()->update(($this->schema ? $this->schema . '.' : '') . $this::$table, $model, $qb);
    }

    /**
     * @throws RepositoryException
     * @throws CDOException
     */
    public function delete(Qb $qb): int|string
    {
        if ($this->isReadonly) {
            throw new RepositoryException('No write access (action delete)');
        }
        return $this->db()->delete(($this->schema ? $this->schema . '.' : '') . $this::$table, $qb);
    }
}
