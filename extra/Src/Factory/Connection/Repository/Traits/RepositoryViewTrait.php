<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Repository\Traits;

use Flytachi\Extra\Src\Factory\Connection\Qb;
use Flytachi\Extra\Src\Factory\Connection\Repository\RepositoryException;
use PDO;
use stdClass;

trait RepositoryViewTrait
{
    /**
     * @param string|null $modelClassName
     * @return mixed
     * @throws RepositoryException
     */
    final public function find(?string $modelClassName = null): mixed
    {
        try {
            if ($modelClassName) {
                $this->modelClassName = $modelClassName;
            }
            $this->limit(1);
            $stmt = $this->db()->prepare($this->buildSql());
            // Bind
            if (array_key_exists('binds', $this->sqlParts)) {
                foreach ($this->sqlParts['binds'] as $hash => $value) {
                    $stmt->bindValue($hash, $value);
                }
            }
            $stmt->execute();
            $this->cleanCache();
            return $stmt->fetchObject($modelClassName ?: $this->modelClassName) ?: null;
        } catch (\Throwable $th) {
            throw new RepositoryException($th->getMessage(), previous: $th);
        }
    }

    /**
     * @param int $column column index (started from 0 index)
     * @return mixed
     * @throws RepositoryException
     */
    final public function findColumn(int $column = 0): mixed
    {
        try {
            $this->limit(1);
            $stmt = $this->db()->prepare($this->buildSql());
            // Bind
            if (array_key_exists('binds', $this->sqlParts)) {
                foreach ($this->sqlParts['binds'] as $hash => $value) {
                    $stmt->bindValue($hash, $value);
                }
            }
            $stmt->execute();
            $this->cleanCache();
            return $stmt->fetchColumn($column);
        } catch (\Throwable $th) {
            throw new RepositoryException($th->getMessage(), previous: $th);
        }
    }

    /**
     * @param string|null $modelClassName
     * @return array<object|stdClass>|null
     * @throws RepositoryException
     */
    final public function findAll(?string $modelClassName = null): ?array
    {
        try {
            if ($modelClassName) {
                $this->modelClassName = $modelClassName;
            }
            $stmt = $this->db()->prepare($this->buildSql());
            // Bind
            if (array_key_exists('binds', $this->sqlParts)) {
                foreach ($this->sqlParts['binds'] as $hash => $value) {
                    $stmt->bindValue($hash, $value);
                }
            }
            $stmt->execute();
            $this->cleanCache();
            return $stmt->fetchAll(PDO::FETCH_CLASS, $modelClassName ?: $this->modelClassName) ?: null;
        } catch (\Throwable $th) {
            throw new RepositoryException($th->getMessage(), previous: $th);
        }
    }

    /**
     * Finds a record by its ID.
     *
     * @param int|string $id The ID of the record to find.
     * @param string|null $modelClassName The class name of the model to use for the find operation. Defaults to null.
     *
     * @return mixed Returns the found record if it exists, or null if it does not.
     */
    final public static function findById(int|string $id, ?string $modelClassName = null): mixed
    {
        return (new static())
            ->where(Qb::eq('id', $id))
            ->find($modelClassName);
    }

    /**
     * Finds a record by its ID or throws an error if the record is not found.
     *
     * @param int|string $id The ID of the record to find.
     * @param string|null $modelClassName The class name of the model to use for the find operation. Defaults to null.
     * @param string $message The error message to be thrown if the record is not found. Defaults to 'Object not found'.
     *
     * @return mixed Returns the found record if it exists.
     * @throws RepositoryException
     */
    final public static function findByIdOrThrow(
        int|string $id,
        ?string $modelClassName = null,
        string $message = 'Object not found'
    ): mixed {
        $obj = static::findById($id);
        if (!$obj) {
            throw new RepositoryException($message);
        }
        return $obj;
    }

    /**
     * Finds records based on a Qb object.
     *
     * @param Qb $qb The Qb object containing the conditions for the find operation.
     * @param string|null $modelClassName The class name of the model to use for the find operation. Defaults to null.
     *
     * @return mixed    Returns the found records if any exist, or null if none are found.
     * @throws RepositoryException
     */
    final public static function findBy(Qb $qb, ?string $modelClassName = null): mixed
    {
        return (new self())->where($qb)->find($modelClassName);
    }

    /**
     * Finds a record using the provided Qb object and throws an error if the record does not exist.
     *
     * @param Qb $qb The Qb object used to search for the record.
     * @param string|null $modelClassName The class name of the model to use for the find operation. Defaults to null.
     * @param string $message The error message to throw if the record is not found. Defaults to 'Object not found'.
     *
     * @return mixed Returns the found record if it exists, or throws
     * @throws RepositoryException
     */
    final public static function findByOrThrow(
        Qb $qb,
        ?string $modelClassName = null,
        string $message = 'Object not found'
    ): mixed {
        $obj = self::findBy($qb);
        if (!$obj) {
            throw new RepositoryException($message);
        }
        return $obj;
    }

    /**
     * Finds multiple records based on a set of conditions.
     *
     * @param null|Qb $qb The conditions to use for finding the records. Defaults to null.
     * @param string|null $modelClassName The class name of the model to use for the find operation. Defaults to null.
     *
     * @return array|false    Returns an array of found records if they exist, or false if no records are found.
     * @throws RepositoryException
     */
    final public static function findAllBy(?Qb $qb = null, ?string $modelClassName = null): array|false
    {
        return (new self())->where($qb)->findAll($modelClassName);
    }
}
