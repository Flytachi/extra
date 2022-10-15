<?php

namespace Extra\Src;

use PDO;
use PDOException;
use ReflectionClass;

class CDO extends PDO
{
    /**
     * 
     * CDO
     * 
     * @version 3.0
     */
    private mixed $debug;

    function __construct(array $params, $debug = false)
    {
        if (is_null($params['DRIVER'])) dieConnection("Connection: driver not found!");
        if (is_null($params['CHARSET'])) dieConnection("Connection: charset not found!");
        if (is_null($params['HOST'])) dieConnection("Connection: host not found!");
        if (is_null($params['PORT'])) dieConnection("Connection: port not found!");
        if (is_null($params['NAME'])) dieConnection("Connection: db name not found!");
        if (is_null($params['USER'])) dieConnection("Connection: username not found!");
        $DNS = $params['DRIVER'] . ":host=".$params['HOST'] . ";port=" . $params['PORT'] . ";dbname=" . $params['NAME'] . ";charset=" . $params['CHARSET'];
        $user = $params['USER'];
        $password = $params['PASS'];
        $this->debug = $debug;
        try {
            parent::__construct($DNS, $user, $password);
            $this->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->SetAttribute(PDO::ATTR_EMULATE_PREPARES, False);
            if ( $this->debug ) $this->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            if($debug) dieConnection($e->getMessage());
            else Route::ErrorPage(500);
        }
    }

    final public function insert(string $table, Model $model): string|false
    {
        $array = objectToArray($model);
        $col = implode(",", array_keys($array));
        $val = ":".implode(", :", array_keys($array));
        try{
            $this->prepare("INSERT INTO $table ($col) VALUES ($val)")->execute($array);
            return $this->lastInsertId();
        }
        catch (PDOException $ex) {
            return $ex->getMessage();
            return ($this->debug) ? $ex->getMessage() : "Ошибка создания элемента.";
        }
    }

    final public function update(string $table, Model $model, int|string|array $pk): int|string
    {
        $array = objectToArray($model);
        $set = "";
        foreach ($array as $key => $value) {
            $array["S_$key"] = $value; unset($array[$key]);
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
            $stm->execute([...$pk, ...$array]);
            return $stm->rowCount();
        } catch (PDOException $ex) {
            return ($this->debug) ? $ex->getMessage() : "Ошибка обновления элемента.";
        }
    }

    final public function delete(string $table, int|string|array $pk): int|string
    {
        $where = '';
        if(!is_array($pk)) $pk = array('id'=>$pk);
        foreach ($pk as $key => $value) {
            if (is_array($value)) {

                $body = '';
                foreach ($value as $vKey => $vValue) {
                    $name = $key . "_in_$vKey";
                    $body .= ":$name,";
                    $pk[$name] = $vValue;
                }
                $where .= " AND `$key` IN (" . rtrim($body, ',') . ")";
                unset($pk[$key]);

            } else $where .= " AND `$key`=:$key";
        }

        // Send
        try {
            $stmt = $this->prepare("DELETE FROM $table WHERE " . ltrim($where, " AND "));
            $stmt->execute($pk);
            return $stmt->rowCount();
        } catch (PDOException $ex) {
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
    
}
