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
            if ($this->CRD_error) $this->errorX($th);
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
            if ($this->CRD_error) $this->errorX($th);
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
            if ($this->CRD_error) $this->errorX($th);
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
            if ($this->CRD_error) $this->errorX($th);
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
            if ($this->CRD_error) $this->errorX($th);
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

?>