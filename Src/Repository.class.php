<?php

namespace Extra\Src;

class Repository
{
    /**
     * 
     * Repository
     * 
     * @version 3.0
     */


    protected string $table;
    protected string $modelName = 'stdClass';
    private array $CRD_SQL = [];
    private string $pk;
    private array $data;
    public CDO $db;
    private bool $CRD_debug;
    
    public function __construct($table_As = '')
    {
        $this->loader();
        if(get_parent_class($this)) {
            if ($table_As) $this->CRD_SQL['as'] = $table_As;
        }else $this->table = $table_As;
        $this->CRD_debug = cfgGet()['GLOBAL_SETTING']['DEBUG'];
        $this->setCfg();
    }

    function __destruct()
    {
        unset($this->CRD_SQL);
        unset($this->db);
    }

    private function loader()
    {
        spl_autoload_register(function($class) {
            $file = dirname(__FILE__, 3) . '/models/' . $class . '.php';
			if (file_exists($file)) require $file;
        });
    }

    /*
    ---------------------------------------------
        SETS AND GETS DATATABLE
    ---------------------------------------------
    */
    public function setCfg(array $cfgDatabase = []): void
    {
        if ($cfgDatabase) {
            $this->db = new CDO($cfgDatabase, $this->CRD_debug);
        } else {
            $this->db = new CDO(cfgGet()['DATABASE'], $this->CRD_debug);
        }
    }

    final public function setPk(string $pk): void
    {
        $this->pk = $pk;
    }
    
    final public function getPk(): string
    {
        return $this->pk;
    }

    final public function setData(array|object $data): void
    {
        $this->data = (array) $data;
    }

    final public function getData(string $item = ''): mixed
    {
        return ($item) ? ($this->data[$item] ?? '') : $this->data;
    }

    final public function setDataItem(string $item, $value = null): void
    {
        $this->data[$item] = $value;
    }

    final public function deleteDataItem(string $item): void
    {
        unset($this->data[$item]);
    }
    
    /*
    ---------------------------------------------
    */
    

    /*  
    ---------------------------------------------
        PARAMETRS SQL
    ---------------------------------------------
    */
    final public function getSearch()
    {
        $this->CRD_SQL['search'] = (isset($_GET['search']) and $_GET['search']) ? 'search=' . $_GET['search'] : "";
        $search = str_replace('search=', '', $this->CRD_SQL['search']);
        return $this->clsDta($search);
    }

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
            if (array_key_exists('limit', $this->CRD_SQL)) {
                $page = (int)(isset($_GET['CRD_page'])) ? (int) $_GET['CRD_page'] : $page = 1;
                $offset = (int) $this->CRD_SQL['limit'] * ($page - 1);
                $sql .= ' LIMIT ' . $this->CRD_SQL['limit'] . ' OFFSET ' . $offset;
            }

            return $sql;
        } catch (\Throwable $th) {
            if ($this->CRD_debug) $this->errorX($th);
            else echo 'Ошибка в генерации скрипта <strong>"SQL"</strong>';
        }
        
    }

    final public function getSql(string $param = null): string|null
    {
        if ($param) {
            return (array_key_exists($param, $this->CRD_SQL)) ? $this->CRD_SQL[$param] : null;
        } else return $this->buildSql();
    }

    final public function Option(string $option): Repository
    {
        $this->CRD_SQL['option'] = $option;
        return $this;
    }

    final public function As(string $table_as): Repository
    {
        $this->CRD_SQL['as'] = $table_as;
        return $this;
    }

    final public function Join(Repository $repository, string $on): Repository
    {
        $context = $repository->table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        $this->CRD_SQL['join'] .= ' JOIN ' . $context;
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'JOIN ' . $context;
        return $this;
    }

    final public function JoinLEFT(Repository $repository, string $on): Repository
    {
        $context = $repository->table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' LEFT JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'LEFT JOIN ' . $context;
        return $this;
    }

    final public function JoinRIGHT(Repository $repository, string $on): Repository
    {
        $context = $repository->table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' RIGHT JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'RIGHT JOIN ' . $context;
        return $this;
    }

    final public function Where(string|array $context): Repository
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

    final public function Union(Repository $repository): Repository
    {
        if (array_key_exists('union', $this->CRD_SQL)) {
            $this->CRD_SQL['union'] .= ' UNION ' . $repository->getSql();
        } else $this->CRD_SQL['union'] = 'UNION ' . $repository->getSql();
        return $this;
    }

    final public function Group(string $context): Repository
    {
        $this->CRD_SQL['group'] = 'GROUP BY ' . $context;
        return $this;
    }

    final public function Order(string $context): Repository
    {
        $this->CRD_SQL['order'] = 'ORDER BY ' . $context;
        return $this;
    }

    final public function Limit(int $limit): Repository
    {
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
            $get = $this->db->query($this->buildSql());
            $get->setFetchMode(\PDO::FETCH_CLASS, $this->modelName);
            return $get->fetch();

        } catch (\Throwable $th) {
            if ($this->CRD_debug) $this->errorX($th);
            else echo 'Ошибка в генерации скрипта <strong>"GET"</strong>';
        }
    }

    final public function getAll(): array
    {
        try {
            $list = $this->db->query($this->buildSql())->fetchAll(\PDO::FETCH_CLASS, $this->modelName);
            return $list;

        } catch (\Throwable $th) {
            if ($this->CRD_debug) $this->errorX($th);
            else echo 'Ошибка в генерации скрипта <strong>"LIST"</strong>';
        }
    }

    // START column is_delete
    final public function getAllDelete(): array
    {
        $as = ($this->getSql('as')) ? $this->getSql('as') . '.' : '';
        if (array_key_exists('where', $this->CRD_SQL)) {
            $this->CRD_SQL['where'] = str_replace('WHERE', 'WHERE ' . $as . 'is_delete = 1 AND ', $this->CRD_SQL['where']);
        } else {
            $this->CRD_SQL['where'] = ' WHERE ' . $as . 'is_delete = 1';
        }
        return $this->getAll();
    }
    
    final public function getAllNotDelete(): array
    {
        $as = ($this->getSql('as')) ? $this->getSql('as') . '.' : '';
        if (array_key_exists('where', $this->CRD_SQL)) {
            $this->CRD_SQL['where'] = str_replace('WHERE', 'WHERE ' . $as . 'is_delete = 0 AND ', $this->CRD_SQL['where']);
        } else {
            $this->CRD_SQL['where'] = ' WHERE ' . $as . 'is_delete = 0';
        }
        return $this->getAll();
    }
    // END column is_delete

    final public function getBy(array $params, string|array $item = ''): mixed
    {
        try {
            $where = '';
            foreach ($params as $key => $value) {

                if(is_array($value)) {
                    $where .= ($where == '') ? "$key IN (" . implode(',', $value) . ") " : "AND $key IN (" . implode(',', $value) . ") ";
                } else {
                    $where .= ($where == '') ? "$key = '$value' " : "AND $key = '$value' ";
                }

            }
            $this->Where($where);
            if (!is_array($item)) return $this->get($item);
            else return call_user_func_array([$this, 'get'], $item);

        } catch (\Throwable $th) {
            if ($this->CRD_debug) $this->errorX($th);
            else echo 'Ошибка в генерации скрипта <strong>"BY"</strong>';
        }
    }

    final public function getById(string $id, string|array $item = ''): mixed
    {
        try {
            $prefix = (array_key_exists('as', $this->CRD_SQL)) ? $this->CRD_SQL['as'].'.' : '';
            $this->Where($prefix . "id = $id");
            if (!is_array($item)) return $this->get($item);
            else return call_user_func_array([$this, 'get'], $item);

        } catch (\Throwable $th) {
            if ($this->CRD_debug) $this->errorX($th);
            else echo 'Ошибка в генерации скрипта <strong>"BY ID"</strong>';
        }
    }

    final public function getId(): mixed
    {
        try {

            $this->Option("id");
            $get = $this->db->query($this->buildSql())->fetchColumn();
            return $get;

        } catch (\Throwable $th) {
            if ($this->CRD_debug) $this->errorX($th);
            else echo 'Ошибка в генерации скрипта <strong>"GET ID"</strong>';
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

    final public function save(array|object $data): string
    {
        $this->setData($data);
        $this->saveBefore();
        $this->saveBody();
        $this->saveAfter();
        return $this->getPk();
    }

    public function saveBefore(): void
    {
        $this->db->beginTransaction();
        $this->setData(CDO::cleanForm($this->getData()));
    }

    public function saveBody(): void
    {
        $object = $this->db->insert($this->table, $this->getData());
        if (!is_numeric($object)) $this->error($object);
        $this->setPk($object);
    }

    public function saveAfter(): void
    {
        $this->db->commit();
    }

    final public function update(string $pk, array|object $data): string
    {
        $this->setPk($pk);
        $this->setData($data);
        $this->updateBefore();
        $this->updateBody();
        $this->updateAfter();
        return $this->getPk();
    }

    public function updateBefore(): void
    {
        $this->db->beginTransaction();
        $this->setData(CDO::cleanForm($this->getData()));
    }

    public function updateBody(): void
    {
        $object = $this->db->update($this->table, $this->getData(), $this->getPk());
        if (!is_numeric($object)) $this->error($object);
    }

    public function updateAfter(): void
    {
        $this->db->commit();
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
        $this->db->beginTransaction();
    }

    public function deleteBody(): void
    {
        $object = $this->db->delete($this->table, $this->getPk());
        if (!is_numeric($object)) $this->error($object);
    }

    public function deleteAfter(): void
    {
        $this->db->commit();
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

    private function errorX(\Throwable $error): never
    {
        $message = "\t" . $error->getMessage();
        foreach ($error->getTrace() as $key => $value) {
            if ($key != 0) {
                $message .= "\n\t\t#" . $key . ' ';  
                if (isset($value['file']))     $message .= $value['file'];
                if (isset($value['line']))     $message .= '(' . $value['line'] . '): ';
                if (isset($value['class']))    $message .= $value['class'];
                if (isset($value['type']))     $message .= $value['type'];
                if (isset($value['function'])) $message .= $value['function'];
            }
        }
        die (parad('CREDO', $message));
    }

    public function error($message): void
    {
        if($this->db->inTransaction()) $this->db->rollBack();
        Route::ErrorResponseJson(array(
            'status' => 'error', 
            'message' => $message,
        ));
    }

}

?>