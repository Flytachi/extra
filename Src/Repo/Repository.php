<?php

namespace Extra\Src\Repo;

use Extra\Src\Artefact\Aegis;
use Extra\Src\Artefact\CDO\CDO;
use Extra\Src\Factory\Entity\Model\ModelBase;
use Extra\Src\Factory\Entity\Model\ModelInterface;
use Extra\Src\HttpCode;
use Extra\Src\Log\Log;
use PDO;
use Throwable;

/**
 * Class Repository
 *
 * The `Repository` class is designed to represent the data layer in your application
 * providing a unified way to perform CRUD operations. It abstracts the interactions
 * with your database using the provided model's properties to construct queries.
 *
 * The Repository methods include:
 *
 * - `__construct(string $table_As = '')`: Initializes a new instance of the Repository with an optional alias for the table.
 * - `db(): CDO`: Returns the `CDO` instance representing the database connection.
 * - `cleanCache(): void`: Clears the cached SQL parameters.
 * - `buildSql(): string`: Constructs a SQL select statement using the cached parameters.
 * - `getSql(string $param = null): string|array|null`: Returns a specific SQL parameter or the constructed SQL if no parameter is specified.
 * - `findColumn(int $column = 0): mixed`: Fetches a column's value from the first matching row of the select statement.
 * - `find(?string $modelClassName = null): mixed`: Fetches the first matching row as an object of the provided model class.
 * - `findAll(?string $modelClassName = null): array|false`: Fetches all matching rows as objects of the provided model class.
 * - `insert(ModelInterface $model): mixed`: Inserts a new row corresponding to the provided model into
 *
 * @version 11.5
 * @author Flytachi
 */
class Repository
{
    use RepositoryORMTrait;
    /** @var string $shardKey Aegis shard key (default => 'db') */
    protected static string $shardKey = 'db';
    /** @var class-string $modelClassName model class name (default => ModelBase::class) */
    protected string $modelClassName = ModelBase::class;
    /** @var bool $isReadonly readonly status (block writing permission) */
    protected bool $isReadonly = false;
    /** @var string|null $schema schema in database */
    protected ?string $schema = null;
    /** @var string $table name of the table in the database */
    public static string $table;
    /** @var array $CRD_SQL sql parameters */
    private array $CRD_SQL = [];

    public function __construct(string $table_As = '')
    {
        if ($table_As) $this->CRD_SQL['as'] = $table_As;
        $shard = Aegis::getShard($this::$shardKey);
        if ($this->schema == null) $this->schema = $shard->getSchema();
        $shard->connect();
    }

    /**
     * @return CDO
     */
    final public function db(): CDO
    {
        return Aegis::db($this::$shardKey);
    }

    final public function cleanCache(): void
    {
        $this->CRD_SQL = [];
    }

    final public function buildSql(): string
    {
        try {
            $sql = 'SELECT ' . $this->prepareSelect();
            $sql .= ' FROM ' . (($this->schema) ? $this->schema . '.' : '') . $this::$table;
            if(array_key_exists('as',     $this->CRD_SQL)) $sql .= ' ' . $this->CRD_SQL['as'];
            if(array_key_exists('join',   $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['join']);
            if(array_key_exists('where',  $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['where']);
            if(array_key_exists('union',  $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['union']);
            if(array_key_exists('group',  $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['group']);
            if(array_key_exists('having', $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['having']);
            if(array_key_exists('order',  $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['order']);
            if(array_key_exists('limit',  $this->CRD_SQL)) $sql .= ' LIMIT ' . trim($this->CRD_SQL['limit']);
            if(array_key_exists('offset', $this->CRD_SQL)) $sql .= ' OFFSET ' . trim($this->CRD_SQL['offset']);
            if(array_key_exists('for', $this->CRD_SQL)) $sql .= ' FOR ' . trim($this->CRD_SQL['for']);
            Log::trace('Repository build:'. $sql);
            return $sql;
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    final public function getSql(string $param = null): string|array|null
    {
        if ($param) {
            return (array_key_exists($param, $this->CRD_SQL)) ? $this->CRD_SQL[$param] : null;
        } else return $this->buildSql();
    }

    /**
     * @param int $column column index (started from 0 index)
     * @return mixed
     */
    final public function findColumn(int $column = 0): mixed
    {
        try {
            $this->limit(1);
            $stmt = $this->db()->prepare($this->buildSql());
            // Bind
            if (array_key_exists('binds', $this->CRD_SQL)) {
                foreach ($this->CRD_SQL['binds'] as $hash => $value)
                    $stmt->bindValue($hash, $value);
            }
            $stmt->execute();
            $this->cleanCache();
            return $stmt->fetchColumn($column);
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    /**
     * @param string|null $modelClassName
     * @return mixed
     */
    final public function find(?string $modelClassName = null): mixed
    {
        try {
            if($modelClassName) $this->modelClassName = $modelClassName;
            $this->limit(1);
            $stmt = $this->db()->prepare($this->buildSql());
            // Bind
            if (array_key_exists('binds', $this->CRD_SQL)) {
                foreach ($this->CRD_SQL['binds'] as $hash => $value)
                    $stmt->bindValue($hash, $value);
            }
            $stmt->execute();
            $this->cleanCache();
            return $stmt->fetchObject($modelClassName ?: $this->modelClassName);
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    /**
     * @param string|null $modelClassName
     * @return array<ModelBase>|false
     */
    final public function findAll(?string $modelClassName = null): array|false
    {
        try {
            if($modelClassName) $this->modelClassName = $modelClassName;
            $stmt = $this->db()->prepare($this->buildSql());
            // Bind
            if (array_key_exists('binds', $this->CRD_SQL)) {
                foreach ($this->CRD_SQL['binds'] as $hash => $value)
                    $stmt->bindValue($hash, $value);
            }
            $stmt->execute();
            $this->cleanCache();
            return $stmt->fetchAll(PDO::FETCH_CLASS, $modelClassName ?: $this->modelClassName);
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    /**
     * Calculate the total size of a table relation in bytes.
     *
     * @return int The size of the relation in bytes.
     */
    final public function relationSize(): int
    {
        try {
            $stmt = $this->db()->prepare("SELECT pg_total_relation_size('" . ((($this->schema) ? $this->schema . '.' : '') . $this::$table) . "')");
            // Bind
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    public function insert(ModelInterface $model): mixed
    {
        if ($this->isReadonly) RepositoryError::throw(HttpCode::INTERNAL_SERVER_ERROR,
            static::class  . ': No write access');

        return $this->db()->insert(($this->schema ? $this->schema . '.' : '') . $this::$table, $model);
    }

    public function insertGroup(ModelInterface ...$models): void
    {
        if ($this->isReadonly) RepositoryError::throw(HttpCode::INTERNAL_SERVER_ERROR,
            static::class  . ': No write access');

        $this->db()->insertGroup(($this->schema ? $this->schema . '.' : '') . $this::$table, ...$models);
    }

    public function update(ModelInterface $model, BKB $bkb): int|string
    {
        if ($this->isReadonly) RepositoryError::throw(HttpCode::INTERNAL_SERVER_ERROR,
            static::class  . ': No write access');
        return $this->db()->update(($this->schema ? $this->schema . '.' : '') . $this::$table, $model, $bkb);
    }

    public function delete(BKB $bkb): int|string
    {
        if ($this->isReadonly) RepositoryError::throw(HttpCode::INTERNAL_SERVER_ERROR,
            static::class  . ': No write access');
        return $this->db()->delete(($this->schema ? $this->schema . '.' : '') . $this::$table, $bkb);
    }

    private function Throwable(Throwable $error): never
    {
        RepositoryError::throw(HttpCode::INTERNAL_SERVER_ERROR,  static::class  . ': ' . $error->getMessage());
    }

    private function prepareSelect(): string
    {
        if (array_key_exists('option', $this->CRD_SQL)) {
            $this->modelClassName = ModelBase::class;
            return $this->CRD_SQL['option'];
        } else {
            if (
                $this->modelClassName === \stdClass::class || $this->modelClassName === ModelBase::class
            ) return '*';
            else {
                $values = [];
                $selection = $this->modelClassName::selection();
                if (array_key_exists('as', $this->CRD_SQL)) $prefix = $this->CRD_SQL['as'] . '.';
                else $prefix = '';
                foreach (get_class_vars($this->modelClassName) as $name => $val) {
                    if (isset($selection[$name]))
                        $values[] = sprintf($selection[$name]::selectionLabel(), $name);
                    else $values[] = $prefix . $name;
                }
                return implode(', ', $values);
            }

        }
    }

}
