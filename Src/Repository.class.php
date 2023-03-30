<?php

namespace Extra\Src;

use PDO;
use Throwable;
use Warframe;

/**
 *  Warframe collection
 *
 *  Repository - a class for working with tables in a database
 *
 *  @version 5.1
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
            $sql = 'SELECT ';
            $sql .= array_key_exists('option', $this->CRD_SQL) ? $this->CRD_SQL['option'] : '*';
            $sql .= ' FROM ' . $this->table;
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

    final public function getSql(string $param = null): string|null
    {
        if ($param) {
            return (array_key_exists($param, $this->CRD_SQL)) ? $this->CRD_SQL[$param] : null;
        } else return $this->buildSql();
    }

    final public function Option(string $option): self
    {
        $this->CRD_SQL['option'] = $option;
        return $this;
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

    final public function Where(string|array $context): self
    {
        $this->CRD_SQL['where'] = 'WHERE ';
        if (is_array($context)) {
            foreach ($context as $key => $value) {
                if(is_array($value)) {
                    $this->CRD_SQL['where'] .= ($this->CRD_SQL['where'] == "WHERE ") ? "$key IN (" . implode(',', $value) . ") " : "AND $key IN (" . implode(',', $value) . ") ";
                } else {
                    $this->CRD_SQL['where'] .= ($this->CRD_SQL['where'] == "WHERE ") ? "$key = '$value' " : "AND $key = '$value' ";
                }
            }
        }else $this->CRD_SQL['where'] .= $context;
        return $this;
    }

    final public function Union(self $repository): self
    {
        if (array_key_exists('union', $this->CRD_SQL)) {
            $this->CRD_SQL['union'] .= ' UNION ' . $repository->getSql();
        } else $this->CRD_SQL['union'] = 'UNION ' . $repository->getSql();
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
        $this->CRD_SQL['limit'] = $limit;
        $this->CRD_SQL['page'] = $page;
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
            $get = Warframe::$db->query($this->buildSql());
            $get->setFetchMode(PDO::FETCH_CLASS, $this->getFetchMode());
            return $get->fetch();
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    final public function getAll(): array|false
    {
        try {
            return Warframe::$db->query($this->buildSql())->fetchAll(PDO::FETCH_CLASS, $this->getFetchMode());
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    // START column is_delete
    final public function getAllDelete(): array|false
    {
        $as = ($this->getSql('as')) ? $this->getSql('as') . '.' : '';
        if (array_key_exists('where', $this->CRD_SQL))
            $this->CRD_SQL['where'] = str_replace('WHERE', 'WHERE ' . $as . 'is_delete = 1 AND ', $this->CRD_SQL['where']);
        else $this->CRD_SQL['where'] = ' WHERE ' . $as . 'is_delete = 1';
        return $this->getAll();
    }
    
    final public function getAllNotDelete(): array|false
    {
        $as = ($this->getSql('as')) ? $this->getSql('as') . '.' : '';
        if (array_key_exists('where', $this->CRD_SQL))
            $this->CRD_SQL['where'] = str_replace('WHERE', 'WHERE ' . $as . 'is_delete = 0 AND ', $this->CRD_SQL['where']);
        else $this->CRD_SQL['where'] = ' WHERE ' . $as . 'is_delete = 0';
        return $this->getAll();
    }
    // END column is_delete

    final public function getBy(array $params, string|array $item = ''): mixed
    {
        try {
            $where = '';
            foreach ($params as $key => $value) {
                if(is_array($value))
                    $where .= ($where == '') ? "$key IN (" . implode(',', $value) . ") " : "AND $key IN (" . implode(',', $value) . ") ";
                else $where .= ($where == '') ? "$key = '$value' " : "AND $key = '$value' ";
            }
            $this->Where($where);
            if (!is_array($item)) return $this->get($item);
            else return call_user_func_array([$this, 'get'], $item);
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    final public function getById(int $id, string|array $item = ''): mixed
    {
        try {
            $prefix = (array_key_exists('as', $this->CRD_SQL)) ? $this->CRD_SQL['as'].'.' : '';
            $this->Where($prefix . "id = $id");
            if (!is_array($item)) return $this->get($item);
            else return call_user_func_array([$this, 'get'], $item);
        } catch (Throwable $th) {
            $this->Throwable($th);
        }
    }

    final public function getId(): mixed
    {
        try {
            $this->Option("id");
            return Warframe::$db->query($this->buildSql())->fetchColumn();
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

}
