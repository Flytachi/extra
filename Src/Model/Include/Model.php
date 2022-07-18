<?php

abstract class Model
{
    /**
     * 
     * Model
     * 
     * @version 9.3
     */

    protected string $table = '';
    
    private string $pk;
    private array $data = [];
    private string $CRD_as = '';
    private string $CRD_sql;
    private string $CRD_data = '*';
    private string $CRD_join = '';
    private string $CRD_where = '';
    private string $CRD_order = '';
    private string $CRD_group = '';
    private string $CRD_search = '';
    private string $CRD_searchGetName = 'CRD_search=';
    private int $CRD_limit = 0;
    private bool $CRD_error = false;
    public CDO $db;

    use 
        CredoQuery,
        CredoParams;


    public function __construct($table_As = null)
    {
        if ($table_As) $this->CRD_as = $table_As;
        $this->debug = cfgGet()['GLOBAL_SETTING']['DEBUG'];
        $this->setCfg();
    }

    function __destruct()
    {
        unset($this->db);   
    }

    /*  
    ---------------------------------------------
        SETS AND GETS DATATABLE
    ---------------------------------------------
    */
    public function setCfg(array $cfgDatabase = null): void
    {
        if ($cfgDatabase) {
            $this->db = new CDO($cfgDatabase, $this->debug);
        } else {
            $this->db = new CDO(cfgGet()['DATABASE'], $this->debug);
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

    final public function getData(String $item = null)
    {
        return ($item == null) ? $this->data : $this->data[$item] ?? null;
    }

    final public function setDataItem(String $item, $value = null): void
    {
        $this->data[$item] = $value;
    }

    final public function deleteDataItem(String $item): void
    {
        unset($this->data[$item]);
    }

    // -----------

    final public function getTable(): string
    {
        return $this->table;
    }

    final public function getTableAs(): string
    {
        return $this->CRD_as;
    }
    
    final public function getSearch()
    {
        $this->CRD_search = (isset($_GET['CRD_search']) and $_GET['CRD_search']) ? $this->CRD_searchGetName.$_GET['CRD_search'] : "";
        $search = str_replace($this->CRD_searchGetName, "", $this->CRD_search);
        return $this->clsDta($search);
    }

    final public function getSql()
    {
        $this->generateSql();
        return $this->CRD_sql;
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
        $this->setData(CDO::toNull($this->getData()));
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
        $this->setData(CDO::toNull($this->getData()));
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

    final public function csrfToken(): void
    {
        $token = bin2hex(random_bytes(24));
        $_SESSION['CSRFTOKEN'] =  $token;
        echo "<input type=\"hidden\" name=\"csrf_token\" value=\"$token\">";
    }

    private function generateSql(): void
    {
        try {
            $this->CRD_sql = "SELECT $this->CRD_data FROM $this->table $this->CRD_as";
            if($this->CRD_join)  $this->CRD_sql .= " " . $this->CRD_join;
            if($this->CRD_where) $this->CRD_sql .= " " . $this->CRD_where;
            if($this->CRD_group) $this->CRD_sql .= " " . $this->CRD_group;
            if($this->CRD_order) $this->CRD_sql .= " " . $this->CRD_order;
            $this->CRD_search = (isset($_GET['CRD_search']) and $_GET['CRD_search']) ? $this->CRD_searchGetName.$_GET['CRD_search'] : "";
        } catch (\Throwable $th) {
            if ($this->CRD_error) $this->errorX($th);
            else echo 'Ошибка в генерации скрипта <strong>"SQL"</strong>';
        }
        
    }

    private function clsDta($value = "")
    {
        if (!is_array($value)) {
            $value = trim($value);
            $value = stripslashes($value);
            $value = strip_tags($value);
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    private function pageAddon(string $url, int $value = 0): string
    {
        $local = $this->urlToArray($url);
        $local['CRD_page'] += $value;
        return $this->arrayToUrl($local);
    }

    private function pageSet(string $url, int $value = 0): string
    {
        $local = $this->urlToArray($url);
        $local['CRD_page'] = $value;
        return $this->arrayToUrl($local);
    }

    private function urlToArray(string $url): array
    {
        $code = explode('?', $url);
        $result = [];
        foreach (explode('&', $code[1]) as $param) {
            if ($param) {
                $value = explode('=', $param);
                $result[$value[0]] = $value[1];
            }
        }
        return $result;
    }

    private function arrayToUrl(array $get): string
    {
        $str = "?";
        foreach ($get as $key => $value) $str .= "$key=$value&";
        return substr($str,0,-1);
    }

    /*  
    ---------------------------------------------
        PANEL
    ---------------------------------------------
    */
    public function panel(): void
    {
        /*
            Получение панели пагинации!
        */
        if ($this->CRD_limit > 0) {
            $this->CRD_totalPages = ceil($this->db->query(substr($this->CRD_sql, 0, strpos($this->CRD_sql, 'LIMIT')))->rowCount() / $this->CRD_limit);
            if ($this->CRD_totalPages <= 1) return;
            $page = (int)(isset($_GET['CRD_page'])) ? $_GET['CRD_page'] : $page = 1;

            if ($page > $this->CRD_totalPages) $page = $this->CRD_totalPages;
            elseif ($page < 1) $page = 1;

            if (empty($_GET['CRD_page'])) $_GET['CRD_page'] = 1;
            $this->CRD_params = $this->arrayToUrl($_GET);

            echo "  <ul class=\"pagination pagination-flat pagination-rounded align-self-center justify-content-center mt-3\" >";
            echo $this->buildPanel($page);
            echo "  </ul>";
        }
    }

    private function buildPanel(int $page): string
    {
        $this->selfP = $this->CRD_firstBack = $this->CRD_nextLast = "";

        // prev
        if ($this->CRD_totalPages > 5) {
            if ($page > 1) $this->CRD_firstBack = "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, -1)."')\" class=\"page-link\">&larr; &nbsp; Prev</a></li>";
        }

        // left
        if ($page <= floor($this->CRD_totalPages / 2)) {

            if ($this->CRD_totalPages == 5) {

                if ($page == 1) {
                    $this->selfP .= "<li class=\"page-item active\"><a onclick=\"credoSearch('$this->CRD_params')\" class=\"page-link\">$page</a></li>";
                    $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, 1)."')\" class=\"page-link\">".($page+1)."</a></li>";
                }elseif($page == 2) {
                    $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, -1)."')\" class=\"page-link\">".($page-1)."</a></li>";
                    $this->selfP .= "<li class=\"page-item active\"><a onclick=\"credoSearch('$this->CRD_params')\" class=\"page-link\">$page</a></li>";
                }
                
            }elseif ($this->CRD_totalPages == 4) {

                if ($page == 1) {
                    $this->selfP .= "<li class=\"page-item active\"><a onclick=\"credoSearch('$this->CRD_params')\" class=\"page-link\">$page</a></li>";
                    $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, 1)."')\" class=\"page-link\">".($page+1)."</a></li>";
                }elseif($page == 2) {
                    $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, -1)."')\" class=\"page-link\">".($page-1)."</a></li>";
                    $this->selfP .= "<li class=\"page-item active\"><a onclick=\"credoSearch('$this->CRD_params')\" class=\"page-link\">$page</a></li>";
                }
                
            }else {
                $this->selfP .= "<li class=\"page-item active\"><a onclick=\"credoSearch('$this->CRD_params')\" class=\"page-link\">$page</a></li>";
                if ($this->CRD_totalPages > 4) $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, 1)."')\" class=\"page-link\">".($page+1)."</a></li>";
            }
        
        }else {
            $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageSet($this->CRD_params, 1)."')\" class=\"page-link\">1</a></li>";
            if ($this->CRD_totalPages > 3) $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageSet($this->CRD_params, 2)."')\" class=\"page-link\">2</a></li>";
        }

        // center
        if ($this->CRD_totalPages == 5) {

            $status = ($page == 3) ? "active" : ""; 
            $this->selfP .= "<li class=\"page-item $status\"><a onclick=\"credoSearch('".$this->pageSet($this->CRD_params, 3)."')\" class=\"page-link\">3</a></li>";
       
        }elseif ($this->CRD_totalPages > 4) {

            if ($page <= floor($this->CRD_totalPages / 2)) $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageSet($this->CRD_params, floor(($this->CRD_totalPages+$page)/2))."')\" class=\"page-link\">...</a></li>";
            else $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageSet($this->CRD_params, floor(($page)/2))."')\" class=\"page-link\">...</a></li>";

        }elseif($this->CRD_totalPages == 3) {

            $status = ($page == 2) ? "active" : ""; 
            $this->selfP .= "<li class=\"page-item $status\"><a onclick=\"credoSearch('".$this->pageSet($this->CRD_params, 2)."')\" class=\"page-link\">2</a></li>";
        
        }
        

        // right
        if ($page > floor($this->CRD_totalPages / 2)) {

            if ($this->CRD_totalPages > 5) $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, -1)."')\" class=\"page-link\">".($page-1)."</a></li>";
            
            if ($this->CRD_totalPages == 5) {

                if ($page == 4) {
                    $this->selfP .= "<li class=\"page-item active\"><a onclick=\"credoSearch('$this->CRD_params')\" class=\"page-link\">$page</a></li>";
                    $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, 1)."')\" class=\"page-link\">".($page+1)."</a></li>";
                }elseif($page == 5) {
                    $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, -1)."')\" class=\"page-link\">".($page-1)."</a></li>";
                    $this->selfP .= "<li class=\"page-item active\"><a onclick=\"credoSearch('$this->CRD_params')\" class=\"page-link\">$page</a></li>";
                }else {
                    $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, 1)."')\" class=\"page-link\">".($page+1)."</a></li>";
                    $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, 2)."')\" class=\"page-link\">".($page+2)."</a></li>";
                }
                
            }elseif ($this->CRD_totalPages == 4) {

                if ($page == 3) {
                    $this->selfP .= "<li class=\"page-item active\"><a onclick=\"credoSearch('$this->CRD_params')\" class=\"page-link\">$page</a></li>";
                    $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, 1)."')\" class=\"page-link\">".($page+1)."</a></li>";
                }elseif($page == 4) {
                    $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, -1)."')\" class=\"page-link\">".($page-1)."</a></li>";
                    $this->selfP .= "<li class=\"page-item active\"><a onclick=\"credoSearch('$this->CRD_params')\" class=\"page-link\">$page</a></li>";
                }
                
            }elseif ($this->CRD_totalPages == 3) {
                $status = ($page == 3) ? "active" : ""; 
                $this->selfP .= "<li class=\"page-item $status\"><a onclick=\"credoSearch('".$this->pageSet($this->CRD_params, 3)."')\" class=\"page-link\">3</a></li>";
            }else $this->selfP .= "<li class=\"page-item active\"><a onclick=\"credoSearch('$this->CRD_params')\" class=\"page-link\">$page</a></li>";

        }else {
            if ($this->CRD_totalPages > 3) $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageSet($this->CRD_params, $this->CRD_totalPages-1)."')\" class=\"page-link\">".($this->CRD_totalPages-1)."</a></li>";
            $this->selfP .= "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageSet($this->CRD_params, $this->CRD_totalPages)."')\" class=\"page-link\">$this->CRD_totalPages</a></li>";
        }
        
        // next
        if ($this->CRD_totalPages > 5) {
            if ($page < $this->CRD_totalPages) $this->CRD_nextLast =  "<li class=\"page-item\"><a onclick=\"credoSearch('".$this->pageAddon($this->CRD_params, 1)."')\" class=\"page-link\">Next &nbsp; &rarr;</a></li>";
        }

        return $this->CRD_firstBack.$this->selfP.$this->CRD_nextLast;
    }
    /*
    ---------------------------------------------
    */

    /*  
    ---------------------------------------------
        COMAND AND ERRORS
    ---------------------------------------------
    */
    final public function showError(Bool $status = false)
    {
        $this->CRD_error = $status;
        return $this;
    }

    final public function stop(): never
    {
        if($this->db->inTransaction()) $this->db->rollBack();
        exit;
    }

    final public function dd()
    {
        parad("Data", $this->getData());
        $this->stop();
    }

    private function errorX($error): never
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

    public function error($message)
    {
        if($this->db->inTransaction()) $this->db->rollBack();
        Route::ErrorResponseJson(array(
            'status' => 'error', 
            'message' => $message,
        ));
    }
    /*
    ---------------------------------------------
    */

}

?>