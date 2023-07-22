<?php

namespace Extra\Src;

use Extra\Src\Type\Cluster;
use PDO;
use PDOException;

/**
 *  Warframe collection
 * 
 *  CDO - update version to PDO
 * 
 *  @version 7.0
 *  @author itachi
 *  @package Extra\Src
 */
class CDO extends PDO
{
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
     * @param bool $debug debug mode
     *
     * @return void
     *
     * @throws PDOException if debugging is enabled, it will return an error message
     */
    function __construct(array $params, bool $debug = false)
    {
        if (is_null($params['DRIVER'])) Route::Throwable(500, 'CDO: Connection - driver not found!');
        if (is_null($params['CHARSET'])) Route::Throwable(500, 'CDO: Connection - charset not found!');
        if (is_null($params['HOST'])) Route::Throwable(500, 'CDO: Connection - host not found!');
        if (is_null($params['PORT'])) Route::Throwable(500, 'CDO: Connection - port not found!');
        if (is_null($params['NAME'])) Route::Throwable(500, 'CDO: Connection - db name not found!');
        if (is_null($params['USER'])) Route::Throwable(500, 'CDO: Connection - username not found!');
        $DNS = $params['DRIVER'] . ":host=".$params['HOST'] . ";port=" . $params['PORT'] . ";dbname=" . $params['NAME'] . ";charset=" . $params['CHARSET'];
        $user = $params['USER'];
        $password = $params['PASS'];
        try {
            parent::__construct($DNS, $user, $password);
            $this->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->SetAttribute(PDO::ATTR_EMULATE_PREPARES, False);
            if ($debug) $this->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            Route::Throwable(500, 'CDO: ' . $e->getMessage());
        }
    }

    /**
     * Create an entry in the database
     * 
     * @param string $table table name in database
     * @param ModelInterface|array $model model or array data
     * 
     * @return string|false
     * 
     * @throws PDOException if debugging is enabled, it will return an error message
     */
    final public function insert(string $table, ModelInterface|array $model): string|false
    {
        if ($model instanceof ModelInterface) {
            $transform = Cluster::transform($model);

            $data = $transform['data'];
            $col = implode(",", array_keys($data));
            $val = '';
            foreach (array_keys($data) as $colValue) {
                if(array_key_exists($colValue, $transform['wrapper']))
                    $val .= str_replace('?', ':' . $colValue, $transform['wrapper'][$colValue]) . ', ';
                else $val .= ':' . $colValue . ', ';
            }
            $val = rtrim($val, " ,");

        } else {
            $data = (array) $model;
            $col = implode(",", array_keys($data));
            $val = ":".implode(", :", array_keys($data));
        }

        try {
            $stmt = $this->prepare("INSERT INTO $table ($col) VALUES ($val)");
            foreach ($data as $keyVal => $paramVal) $stmt->bindValue(':' . $keyVal, $paramVal);
            $stmt->execute();
            $result = $this->lastInsertId();
            if (!is_numeric($result))
                Route::Throwable(500, 'CDO: Error when creating a record in the database (' . $result . ')');
            return $result;
        } catch (PDOException $ex) {
            Route::Throwable(500, 'CDO: Error when creating a record in the database (' . $ex->getMessage() . ')');
        }

    }

    /**
     * Update an entry in the database
     * 
     * @param string $table table name in database
     * @param ModelInterface|array $model data
     * @param int|string|array $pk field 'id' to database
     *  * param int field 'id' to database
     *  * param string field 'id' to database
     *  * param array group(field => value, ...) to database
     * 
     * @return int|string
     * 
     * @throws PDOException if debugging is enabled, it will return an error message
     */
    final public function update(string $table, ModelInterface|array $model, int|string|array $pk): int|string
    {
        if ($model instanceof ModelInterface) {
            $transform = Cluster::transform($model);
            $data = $transform['data'];
            $set = '';
            foreach ($data as $key => $value) {
                $data["S_$key"] = $value; unset($data[$key]);
                if(array_key_exists($key, $transform['wrapper']))
                    $set .= ", `$key`=". str_replace('?', ':S_' . $key, $transform['wrapper'][$key]);
                else $set .= ", `$key`=:S_$key";
            }
        } else {
            $data = $model;
            $set = "";
            foreach ($data as $key => $value) {
                $data["S_$key"] = $value; unset($data[$key]);
                $set .= ", `$key`=:S_$key";
            }
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
            $stmt = $this->prepare("UPDATE $table SET ". ltrim($set, ", ") ." WHERE " . ltrim($where, " AND "));
            foreach ([...$pk, ...$data] as $keyVal => $paramVal) $stmt->bindValue(':' . $keyVal, $paramVal);
            $stmt->execute();
            $result = $stmt->rowCount();
            if (!is_numeric($result))
                Route::Throwable(500, 'CDO: Error when changing a record in the database (' . $result . ')');
            return $result;
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
        if(!is_array($pk)) $pk = ['id' => $pk];
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
            foreach ($pk as $keyVal => $paramVal) $stmt->bindValue(':' . $keyVal, $paramVal);
            $stmt->execute($pk);
            $result = $stmt->rowCount();
            if (!is_numeric($result))
                Route::Throwable(500, 'CDO: Error deleting a record in the database (' . $result . ')');
            return $result;
        } catch (PDOException $ex) {
            Route::Throwable(500, 'CDO: Error deleting a record in the database (' . $ex->getMessage() . ')');
        }
    }
}
