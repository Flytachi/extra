<?php

namespace Extra\Src;

use PDO;
use PDOException;

/**
 *  Warframe collection
 * 
 *  CDO - update version to PDO
 * 
 *  @version 4.0
 *  @author itachi
 */
class CDO extends PDO
{
    /** @var $debug debugging mode */
    private mixed $debug;

    /**
     * Constructor
     *  
     * @param array $params
     *  * @param DRIVER
     *  * @param CHARSET
     *  * @param HOST
     *  * @param PORT
     *  * @param NAME
     *  * @param USER
     * 
     * @param array $debug debug mode
     * 
     * @return void
     * 
     * @throws PDOException if debugging is enabled, it will return an error message
     */
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

    /**
     * Create an entry in the database
     * 
     * @param string $table table name in database
     * @param ModelInterface model data
     * 
     * @return string|false
     * 
     * @throws PDOException if debugging is enabled, it will return an error message
     */
    final public function insert(string $table, ModelInterface $model): string|false
    {
        $array = Wrapper::objectToArray($model);
        $col = implode(",", array_keys($array));
        $val = ":".implode(", :", array_keys($array));
        try {
            $this->prepare("INSERT INTO $table ($col) VALUES ($val)")->execute($array);
            return $this->lastInsertId();
        } catch (PDOException $ex) {
            Route::Throwable(500, 'CDO: Error when creating a record in the database (' . $ex->getMessage() . ')');
        }
    }

    /**
     * Update an entry in the database
     * 
     * @param string $table table name in database
     * @param ModelInterface model data
     * @param int|string|array $pk field 'id' to database
     *  * param int field 'id' to database
     *  * param string field 'id' to database
     *  * param array group(field => value, ...) to database
     * 
     * @return int|string
     * 
     * @throws PDOException if debugging is enabled, it will return an error message
     */
    final public function update(string $table, ModelInterface $model, int|string|array $pk): int|string
    {
        $array = Wrapper::objectToArray($model);
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
            Route::Throwable(500, 'CDO: Error when changing a record in the database (' . $ex->getMessage() . ')');
        }
    }

    /**
     * Delete an entry in the database
     * 
     * @param string $table table name in database
     * @param int|string|array $pk field 'id' to database
     *  * param int field 'id' to database
     *  * param string field 'id' to database
     *  * param array group(field => value, ...) to database
     * 
     * @return int|string
     * 
     * @throws PDOException if debugging is enabled, it will return an error message
     */
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
            Route::Throwable(500, 'CDO: Error deleting a record in the database (' . $ex->getMessage() . ')');
        }
    }

    /**
     * Clean request data
     * 
     * @param string $data request data
     * @return string cleaned request data
     */
    static function clean(string $data): string
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = strip_tags($data);
        return htmlspecialchars($data);
    }
}
