<?php

namespace Extra\Src;

use Extra\Src\CDO\CDN;
use Extra\Src\Type\Cluster;
use PDO;
use Throwable;
use Warframe;

/**
 *  Warframe collection
 *
 *  Repository - a class for working with tables in a database
 *
 *  @version 7.0
 *  @author itachi
 *  @package Extra\Src
 */
class Repository
{
    /** @var string $table name of the table in the database */
    protected string $table;

    /** @var string $pk element id */
    private string $pk;
    /** @var array $CRD_SQL sql parameters */
    private array $CRD_SQL = [];
    /** @var ModelInterface $model element object */
    private ModelInterface $model;
    
    public function __construct($table_As = '')
    {
        $this->loader();
        if(get_parent_class($this)) {
            if ($table_As) $this->CRD_SQL['as'] = $table_As;
        }else $this->table = $table_As;
        $this->cluster();
    }

    private function loader(): void
    {
        spl_autoload_register(function($class) {
            $class = explode("\\", $class);
			if (ROUTE_PLUGIN_SYSTEM && count($class) > 1) {
				$file = PATH_PLUGIN . "/Frame." . $class[0] . "/models/" . $class[1] . '.php';
			} else {
				$file = PATH_APP . '/models/' . $class[0] . '.php';
			}
			if (file_exists($file)) require $file;
        });
    }

    private function cluster(): void
    {
        if (is_null(Warframe::$db)) 
            Warframe::$db = new CDO(Warframe::$cfg['DATABASE'], Warframe::$cfg['GLOBAL_SETTING']['DEBUG']);
    }

    /*
    ---------------------------------------------
        SETS AND GETS DATATABLE
    ---------------------------------------------
    */
    

    final public function setPk(string $pk): void
    {
        $this->pk = $pk;
    }
    
    final public function getPk(): string
    {
        return $this->pk;
    }

    public function setModel(ModelInterface $model): void
    {
        $this->model = $model;
    }

    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    private function getFetchMode(): string
    {
        $model = str_replace('Repository', 'Model', $this::class);
        if (class_exists($model)) {
            if (count($this->CRD_SQL) == 0) return $model;
            else {
                if (
                    array_key_exists('option', $this->CRD_SQL) ||
                    array_key_exists('join', $this->CRD_SQL) ||
                    array_key_exists('union', $this->CRD_SQL)
                )    return __NAMESPACE__ . '\Model';
                else return $model;
            }
        } else return __NAMESPACE__ . '\Model';
    }

    /*
    ---------------------------------------------
    */
    

    /*  
    ---------------------------------------------
        PARAMETERS SQL
    ---------------------------------------------
    */
    final public function buildSql(): string
    {
        try {
            $sql = 'SELECT ' . $this->prepareSelect() . ' FROM ' . $this->table;
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

    final public function Select(string $option): self
    {
        $this->CRD_SQL['option'] = $option;
        return $this;
    }

    /**
     * Old method
     *
     * @param string $option
     * @return self
     */
    final public function Option(string $option): self
    {
        return $this->Select($option);
    }

    final public function As(string $table_as): self
    {
        $this->CRD_SQL['as'] = $table_as;
        return $this;
    }

    final public function Join(self $repository, string $on): self
    {
        $context = $repository->table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'JOIN ' . $context;
        return $this;
    }

    final public function JoinLEFT(self $repository, string $on): self
    {
        $context = $repository->table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' LEFT JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'LEFT JOIN ' . $context;
        return $this;
    }

    final public function JoinRIGHT(self $repository, string $on): self
    {
        $context = $repository->table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' RIGHT JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'RIGHT JOIN ' . $context;
        return $this;
    }

    final public function Where(CDN $cdn): self
    {
        $this->CRD_SQL['where'] = 'WHERE ' . $cdn->getQuery();
        if (array_key_exists('binds', $this->CRD_SQL)) {
            $this->CRD_SQL['binds'] = [...$this->CRD_SQL['binds'], ...$cdn->getCatch()];
        } else $this->CRD_SQL['binds'] = $cdn->getCatch();
        return $this;
    }

    final public function Union(self $repository): self
    {
        if (array_key_exists('union', $this->CRD_SQL)) {
            $this->CRD_SQL['union'] .= ' UNION ' . $repository->getSql();
        } else $this->CRD_SQL['union'] = 'UNION ' . $repository->getSql();
        if (array_key_exists('binds', $this->CRD_SQL)) {
            $this->CRD_SQL['binds'] = [...$this->CRD_SQL['binds'], ...$repository->getSql('binds')];
        } else $this->CRD_SQL['binds'] = $repository->getSql('binds');
        return $this;
    }

    final public function Group(string $context): self
    {
        $this->CRD_SQL['group'] = 'GROUP BY ' . $context;
        return $this;
    }

    final public function Order(string $context): self
    {
        $this->CRD_SQL['order'] = 'ORDER BY ' . $context;
        return $this;
    }

    final public function Limit(int $limit, int $page = 1): self
    {
        if ($page < 1) $this->error('page < 1');
        $this->CRD_SQL['page'] = $page;
        $this->CRD_SQL['limit'] = $limit;
        return $this;
    }

    /*
    ---------------------------------------------
    */


    /*  
    ---------------------------------------------
        QUERY
    ---------------------------------------------
    */

    final public function get(string ...$items): mixed
    {
        try {
            if ($data = implode(',', $items)) $this->Option($data);
            $stmt = Warframe::$db->prepare($this->buildSql());
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

    final public function getAll(): array|false
    {
        try {
            $stmt = Warframe::$db->prepare($this->buildSql());
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

    final public function getBy(CDN $cdn, string|array $item = ''): mixed
    {
        try {
            $this->CRD_SQL['where'] = 'WHERE ' . $cdn->getQuery();
            $this->CRD_SQL['binds'] = $cdn->getCatch();
            if (!is_array($item)) return $this->get($item);
            else return call_user_func_array([$this, 'get'], $item);
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    /*
    ---------------------------------------------
    */


    /*  
    ---------------------------------------------
        CRUD
    ---------------------------------------------
    */

    final public function save(ModelInterface $model): string
    {
        $this->setModel($model);
        $this->saveBefore();
        $this->saveBody();
        $this->saveAfter();
        return $this->getPk();
    }

    public function saveBefore(): void
    {
        Warframe::$db->beginTransaction();
    }

    public function saveBody(): void
    {
        $object = Warframe::$db->insert($this->table, $this->getModel());
        $this->setPk($object);
    }

    public function saveAfter(): void
    {
        Warframe::$db->commit();
    }

    final public function update(string $pk, ModelInterface $model): string
    {
        $this->setPk($pk);
        $this->setModel($model);
        $this->updateBefore();
        $this->updateBody();
        $this->updateAfter();
        return $this->getPk();
    }

    public function updateBefore(): void
    {
        Warframe::$db->beginTransaction();
    }

    public function updateBody(): void
    {
        Warframe::$db->update($this->table, $this->getModel(), $this->getPk());
    }

    public function updateAfter(): void
    {
        Warframe::$db->commit();
    }

    final public function delete(string $pk): string
    {
        $this->setPk($pk);
        $this->deleteBefore();
        $this->deleteBody();
        $this->deleteAfter();
        return $this->getPk();
    }

    public function deleteBefore(): void
    {
        Warframe::$db->beginTransaction();
    }

    public function deleteBody(): void
    {
        Warframe::$db->delete($this->table, $this->getPk());
    }

    public function deleteAfter(): void
    {
        Warframe::$db->commit();
    }

    /*
    ---------------------------------------------
    */

    private function clsDta(array|string $value): array|string
    {
        if (!is_array($value)) {
            $value = trim($value);
            $value = stripslashes($value);
            $value = strip_tags($value);
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    private function Throwable(Throwable $error): never
    {
        Route::Throwable(500,  'Repository: ' . $error->getMessage());
    }

    public function error($message): void
    {
        if(Warframe::$db->inTransaction()) Warframe::$db->rollBack();
        Route::Throwable(500,  'Repository: ' . $message);
    }

    private function prepareSelect(): string
    {
        if (array_key_exists('option', $this->CRD_SQL)) return $this->CRD_SQL['option'];
        else {
            $values = [];
            if (array_key_exists('as', $this->CRD_SQL)) $prefix = $this->CRD_SQL['as'] . '.';
            else $prefix = '';

            $reflection = new \ReflectionClass($this->getFetchMode());
            foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
                $values[] = $prefix.Cluster::meta($reflectionProperty);
            }

            return implode(', ', $values);
        }
    }

}
