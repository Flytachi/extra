<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\Repository;

use Flytachi\Extra\Src\Factory\Connection\CDO\CDO;
use Flytachi\Extra\Src\Factory\Connection\ConnectionPool;
use Flytachi\Extra\Src\Factory\Connection\Qb;
use Flytachi\Extra\Src\Factory\Connection\Repository\Interfaces\RepositoryInterface;
use Flytachi\Extra\Src\Factory\Stereotype;

abstract class RepositoryCore extends Stereotype implements RepositoryInterface
{
    /** @var class-string $dbConfigClassName dbConfig class name (default => DbConfig::class) */
    protected string $dbConfigClassName;
    /** @var class-string $modelClassName object class name (default => \stdClass::class) */
    protected string $modelClassName = \stdClass::class;
    /** @var bool $isReadonly readonly status (block writing permission) */
    protected bool $isReadonly = false;
    /** @var string|null $schema schema in database */
    protected ?string $schema = null;
    /** @var string $table name of the table in the database */
    public static string $table;
    /** @var array $sqlParts sql parameters */
    protected array $sqlParts = [];

    final public function __construct()
    {
        parent::__construct();
        if (!isset($this->dbConfigClassName)) {
            throw new RepositoryException(static::class . ' $dbConfigClassName must be set by the child class');
        }
        $config = ConnectionPool::getConfigDb($this->dbConfigClassName);
        if ($this->schema == null) {
            $this->schema = $config->getSchema();
        }
        $config->connect();
    }

    /**
     * @return CDO
     */
    final public function db(): CDO
    {
        return ConnectionPool::db($this->dbConfigClassName);
    }

    /**
     * @return string
     */
    final public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @throws RepositoryException
     */
    final public function buildSql(): string
    {
        try {
            $parts = [
                'SELECT ' . $this->prepareSelect(),
                'FROM ' . (($this->schema) ? $this->schema . '.' : '') . static::$table
            ];

            foreach (['as', 'join', 'where', 'union', 'group', 'having', 'order'] as $key) {
                if (isset($this->sqlParts[$key])) {
                    $parts[] = trim($this->sqlParts[$key]);
                }
            }
            if (isset($this->sqlParts['limit'])) {
                $parts[] = 'LIMIT ' . $this->sqlParts['limit'];
            }
            if (isset($this->sqlParts['offset'])) {
                $parts[] = 'OFFSET ' . $this->sqlParts['offset'];
            }
            if (isset($this->sqlParts['for'])) {
                $parts[] = 'FOR ' . $this->sqlParts['for'];
            }

            $query = implode(' ', $parts);
            $this->logger->debug('Repository build:' . $query);
            return $query;
        } catch (\Throwable $th) {
            throw new RepositoryException($th->getMessage(), previous: $th);
        }
    }

    /**
     * @throws RepositoryException
     */
    final public function getSql(string $param = null): mixed
    {
        if ($param) {
            return (isset($this->sqlParts[$param])) ? $this->sqlParts[$param] : null;
        } else {
            return $this->buildSql();
        }
    }

    final protected function cleanCache(): void
    {
        $this->sqlParts = [];
    }

    private function prepareSelect(): string
    {
        if (isset($this->sqlParts['option'])) {
            $this->modelClassName = \stdClass::class;
            return $this->sqlParts['option'];
        } else {
            return '*';
        }
    }

    /**
     * @param string $option
     * @return static
     */
    final public function select(string $option): static
    {
        $this->sqlParts['option'] = $option;
        return $this;
    }

    /**
     * @param string $alias
     * @return static
     */
    final public function as(string $alias): static
    {
        $this->sqlParts['as'] = $alias;
        return $this;
    }

    /**
     * @param RepositoryInterface $repository
     * @param string $on
     * @return static
     */
    final public function join(RepositoryInterface $repository, string $on): static
    {
        $context = (($repository->schema) ? $repository->schema . '.' : '') . $repository::$table
            . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (isset($this->sqlParts['join'])) {
            $this->sqlParts['join'] .= ' JOIN ' . $context;
        } else {
            $this->sqlParts['join'] = 'JOIN ' . $context;
        }
        return $this;
    }

    /**
     * @param RepositoryInterface $repository
     * @param string $on
     * @return static
     */
    final public function joinLeft(RepositoryInterface $repository, string $on): static
    {
        $context = (($repository->schema) ? $repository->schema . '.' : '') . $repository::$table
            . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (isset($this->sqlParts['join'])) {
            $this->sqlParts['join'] .= ' LEFT JOIN ' . $context;
        } else {
            $this->sqlParts['join'] = 'LEFT JOIN ' . $context;
        }
        return $this;
    }

    /**
     * @param RepositoryInterface $repository
     * @param string $on
     * @return static
     */
    final public function joinRight(RepositoryInterface $repository, string $on): static
    {
        $context = (($repository->schema) ? $repository->schema . '.' : '') . $repository::$table
            . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (isset($this->sqlParts['join'])) {
            $this->sqlParts['join'] .= ' RIGHT JOIN ' . $context;
        } else {
            $this->sqlParts['join'] = 'RIGHT JOIN ' . $context;
        }
        return $this;
    }

    /**
     * @param null|Qb $qb
     * @return static
     */
    final public function where(?Qb $qb): static
    {
        if (!is_null($qb)) {
            if ($qb->getQuery()) {
                $this->sqlParts['where'] = 'WHERE ' . $qb->getQuery();
                if (isset($this->sqlParts['binds'])) {
                    $this->sqlParts['binds'] = [...$this->sqlParts['binds'], ...$qb->getCache()];
                } else {
                    $this->sqlParts['binds'] = $qb->getCache();
                }
            }
        }
        return $this;
    }

    /**
     * @param RepositoryInterface $repository
     * @return static
     */
    final public function union(RepositoryInterface $repository): static
    {
        if (isset($this->sqlParts['union'])) {
            $this->sqlParts['union'] .= ' UNION ' . $repository->getSql();
        } else {
            $this->sqlParts['union'] = 'UNION ' . $repository->getSql();
        }
        if (isset($this->sqlParts['binds'])) {
            $this->sqlParts['binds'] = [...$this->sqlParts['binds'], ...$repository->getSql('binds')];
        } else {
            $this->sqlParts['binds'] = $repository->getSql('binds');
        }
        return $this;
    }

    /**
     * @param string $context
     * @return static
     */
    final public function groupBy(string $context): static
    {
        $this->sqlParts['group'] = 'GROUP BY ' . $context;
        return $this;
    }

    /**
     * @param string $context
     * @return static
     */
    final public function having(string $context): static
    {
        $this->sqlParts['having'] = 'HAVING ' . $context;
        return $this;
    }

    /**
     * @param string $context
     * @return static
     */
    final public function orderBy(string $context): static
    {
        $this->sqlParts['order'] = 'ORDER BY ' . $context;
        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return static
     */
    final public function limit(int $limit, int $offset = 0): static
    {
        if ($limit < 1) {
            throw new \TypeError('limit < 1');
        }
        if ($offset < 0) {
            throw new \TypeError('offset < 0');
        }
        $this->sqlParts['limit'] = $limit;
        $this->sqlParts['offset'] = $offset;
        return $this;
    }

    /**
     * @param string $context
     * @return static
     */
    final public function forBy(string $context): static
    {
        $this->sqlParts['for'] = $context;
        return $this;
    }
}
