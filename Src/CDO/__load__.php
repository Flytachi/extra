<?php

class CDO extends PDO
{
    /**
     * 
     * CDO
     * 
     * @version 1.0
     */


    function __construct(array $params, $debug = false)
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
        try {
            parent::__construct($this->DNS, $this->user, $this->password);
            $this->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->SetAttribute(PDO::ATTR_EMULATE_PREPARES, False);
            if ( $this->debug ) $this->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            if($debug) dieConnection($e->getMessage());
            else Route::ErrorPage(500);
        }
    }

    final public function insert(string $table, array $post): int|string
    {
        $col = implode(",", array_keys($post));
        $val = ":".implode(", :", array_keys($post));
        try{
            $this->prepare("INSERT INTO $table ($col) VALUES ($val)")->execute($post);
            return $this->lastInsertId();
        }
        catch (\PDOException $ex) {
            return $ex->getMessage();
        }
    }

    final public function update(string $table, array $post, int|string|array $pk): int|string
    {
        // Set
        $set = "";
        foreach ($post as $key => $value) {
            $post["S_$key"] = $value; unset($post[$key]);
            $set .= ", `$key`=:S_$key";
        }

        // Where
        $where = "";
        if(!is_array($pk)) $pk = array('id'=>$pk);
        foreach ($pk as $key => $value) {
            $pk["W_$key"] = $value; unset($pk[$key]);
            $where .= " AND `$key`=:W_$key";
        }

        // Send
        try {
            $stm = $this->prepare("UPDATE $table SET ". ltrim($set, ", ") ." WHERE " . ltrim($where, " AND "));
            $stm->execute(array_merge($pk,$post));
            return $stm->rowCount();
        } catch (\PDOException $ex) {
            return ($this->debug) ? $ex->getMessage() : "Ошибка обновления элемента.";
        }
    }

    final public function delete(string $table, int|string|array $pk): int|string
    {
        $where = '';
        if(!is_array($pk)) $pk = array('id'=>$pk);
        foreach (array_keys($pk) as $key) $where .= " AND `$key`=:$key";

        // Send
        try {
            $stmt = $this->prepare("DELETE FROM $table WHERE " . ltrim($where, " AND "));
            $stmt->execute($pk);
            return $stmt->rowCount();
        } catch (\PDOException $ex) {
            return ($this->debug) ? $ex->getMessage() : "Ошибка удаления элемента.";
        }
    }

    static function clean(array|string|int $value = ""): array|string|int
    {
        if (!is_array($value)) {
            $value = trim($value);
            $value = stripslashes($value);
            $value = strip_tags($value);
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    static function cleanForm(array $array): array
    {
        foreach ($array as $key => $value) {
            $array[$key] = CDO::clean($value);
        };
        return $array;
    }

    static function toNull(array $array): array
    {
        foreach ($array as $key => $value) {
            if(!$value) $array[$key] = null;
        }
        return $array;
    }

}

?>