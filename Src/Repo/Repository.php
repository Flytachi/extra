<?php

namespace Extra\Src\Repo;

use Extra\Src\Artefact\Aegis;
use Extra\Src\Artefact\ArtefactError;
use Extra\Src\CDO\CDN;
use Extra\Src\CDO\CDO;
use Extra\Src\Enum\HttpCode;
use Extra\Src\Log\Log;
use Extra\Src\Model\ModelBase;
use Extra\Src\Model\ModelInterface;
use Extra\Src\Type\Cluster;
use Extra\Warframe;
use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Throwable;

/**
 *  Warframe collection
 *
 *  Repository - a class for working with tables in a database
 *
 *  @version 10.0
 *  @author itachi
 *  @package Extra\Src
 */
class Repository
{
    use RepositoryORMTrait;
    /** @var string $shardKey Aegis shard key */
    protected static string $shardKey = 'db';
    /** @var string $table name of the table in the database */
    public static string $table;

    /** @var array $CRD_SQL sql parameters */
    private array $CRD_SQL = [];

    public function __construct(string $table_As = '')
    {
        if(get_parent_class($this)) {
            if ($table_As) $this->CRD_SQL['as'] = $table_As;
        }else self::$table = $table_As;
        try {
            Warframe::setDb($this::$shardKey, Aegis::getShard($this::$shardKey));
        } catch (ArtefactError $err) {
            RepositoryError::throw(HttpCode::from($err->getCode()), static::class . ': ' . $err->getMessage());
        }
    }

    /**
     * @return CDO
     */
    final public function db(): CDO
    {
        return Warframe::db($this::$shardKey);
    }

    /**
     * @throws ReflectionException
     */
    private function getFetchMode(): string
    {
        $property = new ReflectionProperty($this, 'model');

        if (array_key_exists('0', $property->getAttributes())) {
            $model = '\\'. $property->getAttributes()[0]->getName();
            if (class_exists($model)) {
                if (count($this->CRD_SQL) == 0) return $model;
                else {
                    if (
                        array_key_exists('option', $this->CRD_SQL) ||
                        array_key_exists('join', $this->CRD_SQL) ||
                        array_key_exists('union', $this->CRD_SQL)
                    )    return ModelBase::class;
                    else return $model;
                }
            } else return ModelBase::class;
        }
        else return ModelBase::class;
    }


    final public function cleanCache(): void
    {
        $this->CRD_SQL = [];
    }

    final public function buildSql(): string
    {
        try {
            $sql = 'SELECT ' . $this->prepareSelect() . ' FROM ' . $this::$table;
            if(array_key_exists('as', $this->CRD_SQL)) $sql .= ' ' . $this->CRD_SQL['as'];
            if(array_key_exists('join', $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['join']);
            if(array_key_exists('where', $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['where']);
            if(array_key_exists('union', $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['union']);
            if(array_key_exists('group', $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['group']);
            if(array_key_exists('order', $this->CRD_SQL)) $sql .= ' ' . trim($this->CRD_SQL['order']);
            if(array_key_exists('limit', $this->CRD_SQL)) {
                $offset = (int) $this->CRD_SQL['limit'] * ($this->CRD_SQL['page'] - 1);
                $sql .= ' LIMIT ' . $this->CRD_SQL['limit'] . ' OFFSET ' . $offset;
            }
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
     * @param string ...$items
     * @return mixed
     */
    final public function get(string ...$items): mixed
    {
        try {
            if ($data = implode(',', $items)) $this->Select($data);
            $stmt = $this->db()->prepare($this->buildSql());
            $stmt->setFetchMode(PDO::FETCH_CLASS, $this->getFetchMode());
            // Bind
            if (array_key_exists('binds', $this->CRD_SQL)) {
                foreach ($this->CRD_SQL['binds'] as $hash => $value)
                    $stmt->bindValue($hash, $value);
            }
            $stmt->execute();
            return $stmt->fetch();
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    /**
     * @return array|false
     */
    final public function getAll(): array|false
    {
        try {
            $stmt = $this->db()->prepare($this->buildSql());
            $stmt->setFetchMode(PDO::FETCH_CLASS, $this->getFetchMode());
            // Bind
            if (array_key_exists('binds', $this->CRD_SQL)) {
                foreach ($this->CRD_SQL['binds'] as $hash => $value)
                    $stmt->bindValue($hash, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    /**
     * @param CDN $cdn
     * @param string|array $item
     * @return mixed
     */
    final public function getBy(CDN $cdn, string|array $item = ''): mixed
    {
        try {
            $this->CRD_SQL['where'] = 'WHERE ' . $cdn->getQuery();
            $this->CRD_SQL['binds'] = $cdn->getCache();
            if (!is_array($item)) return $this->get($item);
            else return call_user_func_array([$this, 'get'], $item);
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    public function insert(ModelInterface $model): mixed
    {
        return $this->db()->insert($this::$table, $model);
    }

    public function update(ModelInterface $model, mixed $pk): int|string
    {
        return $this->db()->update($this::$table, $model, $pk);
    }

    public function delete(int|string|array $pk): int|string
    {
        return $this->db()->delete($this::$table, $pk);
    }

    private function Throwable(Throwable $error): never
    {
        RepositoryError::throw(HttpCode::INTERNAL_SERVER_ERROR,  static::class  . ': ' . $error->getMessage());
    }

    /**
     * @throws ReflectionException
     */
    private function prepareSelect(): string
    {
        if (array_key_exists('option', $this->CRD_SQL)) return $this->CRD_SQL['option'];
        else {
            $values = [];
            if (array_key_exists('as', $this->CRD_SQL)) $prefix = $this->CRD_SQL['as'] . '.';
            else $prefix = '';

            $reflection = new ReflectionClass($this->getFetchMode());

            foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
                $values[] = $prefix.Cluster::meta($reflectionProperty);
            }

            return implode(', ', $values);
        }
    }

}
