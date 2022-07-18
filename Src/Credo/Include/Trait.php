<?php

trait CredoQuery
{
    final public function get(String ...$items)
    {
        try {

            if ($data = implode(',', $items)) $this->Data($data);
            $this->generateSql();
            $get = $this->db->query($this->CRD_sql)->fetch(\PDO::FETCH_OBJ);
            return $get;

        } catch (\Throwable $th) {
            if ($this->CRD_error) $this->error($th);
            else echo 'Ошибка в генерации скрипта <strong>"GET"</strong>';
        }
    }

    final public function by(Array $params, $item = '')
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
            if ($this->CRD_error) $this->error($th);
            else echo 'Ошибка в генерации скрипта <strong>"BY"</strong>';
        }
    }

    final public function byId(Int $id, $item = '')
    {
        try {

            $this->Where("id = $id");
            if (!is_array($item)) return $this->get($item);
            else return call_user_func_array([$this, 'get'], $item);

        } catch (\Throwable $th) {
            if ($this->CRD_error) $this->error($th);
            else echo 'Ошибка в генерации скрипта <strong>"BY ID"</strong>';
        }
    }

    final public function list(Bool $counter = false)
    {
        try {
            $this->generateSql();
            
            if ($this->CRD_limit) {
                $page = (int)(isset($_GET['CRD_page'])) ? (int) $_GET['CRD_page'] : $page = 1;
                $offset = (int) $this->CRD_limit * ($page - 1);
                $this->CRD_sql .= " LIMIT $this->CRD_limit OFFSET $offset";
            }

            $list = $this->db->query($this->CRD_sql)->fetchAll(\PDO::FETCH_OBJ);
            if ($counter) {
                $off_count = (($this->CRD_limit) ? $offset : 0) + 1;
                foreach ($list as $key => $value) $list[$key]->{'count'} = $off_count++;
            }
            return $list;

        } catch (\Throwable $th) {
            if ($this->CRD_error) $this->error($th);
            else echo 'Ошибка в генерации скрипта <strong>"LIST"</strong>';
        }
    }

    final public function getId()
    {
        try {

            $this->Data("id");
            $this->generateSql();
            $get = $this->db->query($this->CRD_sql)->fetchColumn();
            return $get;

        } catch (\Throwable $th) {
            if ($this->CRD_error) $this->error($th);
            else echo 'Ошибка в генерации скрипта <strong>"GET ID"</strong>';
        }
        
    }

}

trait CredoParams
{
    final public function as(String $context)
    {
        /*
            Установка столбцов которые хотим вытащить, по умолчаню все!
        */
        $this->CRD_as = $context;
        return $this;
    }

    final public function Data(String $context = "*")
    {
        /*
            Установка столбцов которые хотим вытащить, по умолчаню все!
        */
        $this->CRD_data = $context;
        return $this;
    }

    final public function Limit(Int $limit = null) 
    {
        /*
            Установка Лимита строк на странице
        */
        $this->CRD_limit = $limit;
        return $this;
    }

    final public function Join(Model $model, String $on)
    {
        /*
            Установка дополнений в скрипе!
            До WHERE!
        */
        $context = $model->getTable() . ' ' . $model->getTableAs() . " ON(" . $on . ")";
        $this->CRD_join .= " JOIN " . $context;
        return $this;
    }

    final public function JoinLEFT(Model $model, String $on)
    {
        /*
            Установка дополнений в скрипе!
            До WHERE!
        */
        $context = $model->getTable() . ' ' . $model->getTableAs() . " ON(" . $on . ")";
        $this->CRD_join .= " LEFT JOIN " . $context;
        return $this;
    }

    final public function JoinRIGHT(Model $model, String $on)
    {
        /*
            Установка дополнений в скрипе!
            До WHERE!
        */
        $context = $model->getTable() . ' ' . $model->getTableAs() . " ON(" . $on . ")";
        $this->CRD_join .= " RIGHT JOIN " . $context;
        return $this;
    }

    final public function Where($context)
    {
        /*
            Установка зависимостей!
        */
        if (is_array($context)) {
            if ($this->CRD_search) $this->CRD_where = "WHERE " . $context[1];
            else $this->CRD_where = "WHERE " . $context[0];
            return $this;
        }else $this->CRD_where = "WHERE " . $context;
        return $this;
    }

    final public function Wr($context)
    {
        /*
            Установка зависимостей!
        */
        if (is_array($context)) {
            
            $this->CRD_where = "WHERE ";
            foreach ($context as $key => $value) {

                if(is_array($value)) {
                    $this->CRD_where .= ($this->CRD_where == "WHERE ") ? "$key IN (" . implode(',', $value) . ") " : "AND $key IN (" . implode(',', $value) . ") ";
                } else {
                    $this->CRD_where .= ($this->CRD_where == "WHERE ") ? "$key = '$value' " : "AND $key = '$value' ";
                }

            }

        }else $this->CRD_where = "WHERE " . $context;
        return $this;
    }

    final public function Order(String $context)
    {
        /*
            Установка порядка сортировки!
        */
        $this->CRD_order = "ORDER BY " . $context;
        return $this;
    }

    final public function Group(String $context)
    {
        /*
            Установка групировки!
        */
        $this->CRD_group = "GROUP BY " . $context;
        return $this;
    }
}

trait CredoPanel
{
    public function panel()
    {
        /*
            Получение панели пагинации!
        */
        if ($this->CRD_limit > 0) {
            $this->CRD_totalPages = ceil($this->db->query(substr($this->CRD_sql, 0, strpos($this->CRD_sql, 'LIMIT')))->rowCount() / $this->CRD_limit);
            if ($this->CRD_totalPages <= 1) return 0;
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

    private function buildPanel(int $page)
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
}

trait CredoHelp
{
    
    final public function showError(Bool $status = false)
    {
        $this->CRD_error = $status;
        return $this;
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
}

?>