<?php

class Connect
{
    private String $DNS;
    private String $user;
    private String $password;
    private Bool $debug;

    function __construct(Array $params, $debug = false)
    {
        if (is_null($params['DRIVER'])) dieConnection("Connection: driver not found!");
        if (is_null($params['CHARSET'])) dieConnection("Connection: charset not found!");
        if (is_null($params['HOST'])) dieConnection("Connection: host not found!");
        if (is_null($params['PORT'])) dieConnection("Connection: port not found!");
        if (is_null($params['NAME'])) dieConnection("Connection: db name not found!");
        if (is_null($params['USER'])) dieConnection("Connection: username not found!");
        $this->DNS = $params['DRIVER'] . ":host=".$params['HOST'] . ";port=" . $params['PORT'] . ";dbname=" . $params['NAME'] . ";charset=" . $params['CHARSET'];
        $this->user = $params['USER'];
        $this->password = $params['PASS'];
        $this->debug = $debug;
    }

    public function connection()
    {
        try {
            $db = new PDO($this->DNS, $this->user, $this->password);
            $db->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->SetAttribute(PDO::ATTR_EMULATE_PREPARES, False);
            if ( $this->debug ) $db->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $db;
        } catch (\PDOException $e) {
            dieConnection($e->getMessage());
        }
    }

    final public function cInsert(String $tb, Array $post)
    {
        $col = implode(",", array_keys($post));
        $val = ":".implode(", :", array_keys($post));
        $sql = "INSERT INTO $tb ($col) VALUES ($val)";
        try{
            $db = $this->connection();
            $db->prepare($sql)->execute($post);
            return $db->lastInsertId();
        }
        catch (\PDOException $ex) {
            return $ex->getMessage();
        }
    }

    final public function cUpdate(string $tb, array $post, $pk)
    {
        foreach (array_keys($post) as $key) {
            if (isset($col)) {
                $col .= ", ".$key."=:".$key;
            }else{
                $col = $key."=:".$key;
            }
        }
        if (is_array($pk)) {
            foreach ($pk as $key => $value) {
                if (is_array($value)) {
                    if (isset($filter)) {
                        $filter .= " AND ".$key." IN (".implode(',', $value).")";
                    }else{
                        $filter = $key." IN (".implode(',', $value).")";
                    }
                } else {
                    if (isset($filter)) {
                        $filter .= " AND ".$key."=".$value;
                    }else{
                        $filter = $key."=".$value;
                    }
                }
            }
            $sql = "UPDATE $tb SET $col WHERE $filter";
        }else {
            $sql = "UPDATE $tb SET $col WHERE id = $pk";
        }
        try{
            $db = $this->connection();
            $stm = $db->prepare($sql)->execute($post);
            return $stm;
        }
        catch (\PDOException $ex) {
            return $ex->getMessage();
        }
    }

    final public function cDelete($tb, $pk, $name_pk = null)
    {
        $name_pk = ($name_pk) ? $name_pk : "id";
        $db = $this->connection();
        $stmt = $db->prepare("DELETE FROM $tb WHERE $name_pk = :item");
        $stmt->bindValue(':item', $pk);
        $stmt->execute();
        return $stmt->rowCount();
    }

    static function clean($value = "")
    {
        if (!is_array($value)) {
            $value = trim($value);
            $value = stripslashes($value);
            $value = strip_tags($value);
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    static function cleanForm(Array $array) 
    {
        foreach ($array as $key => $value) {
            $array[$key] = Connect::clean($value);
        };
        return $array;
    }

    static function toNull(Array $array)
    {
        foreach ($array as $key => $value) {
            if(!$value){
                $array[$key] = null;
            }
        }
        return $array;
    }

}

?>