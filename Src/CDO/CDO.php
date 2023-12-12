<?php

namespace Extra\Src\CDO;

use Extra\Src\Artefact\Shard;
use Extra\Src\Enum\HttpCode;
use Extra\Src\Log\Log;
use Extra\Src\Model\ModelInterface;
use Extra\Src\Type\Cluster;
use PDO;
use PDOException;

/**
 *  Warframe collection
 *
 *  CDO - update version to PDO
 *
 *  @version 11.0
 *  @author itachi
 *  @package Extra\Src
 */
class CDO extends PDO
{
    /**
     * Constructor
     *
     * @param Shard $shard
     * @param bool $debug debug mode
     *
     * @return void
     */
    function __construct(Shard $shard, bool $debug = false)
    {
        try {
            parent::__construct($shard->getDNS(), $shard->getUsername(), $shard->getPassword());
            $this->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->SetAttribute(PDO::ATTR_EMULATE_PREPARES, False);
            if ($debug) $this->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            Log::trace('CDO connection:' . $shard->getDNS());
        } catch (PDOException $e) {
            CDOError::throw(HttpCode::INTERNAL_SERVER_ERROR, 'CDO: ' . $e->getMessage());
        }
    }

    /**
     * Create an entry in the database
     *
     * @param string $table table name in database
     * @param ModelInterface|array $model model or array data
     *
     * @return mixed
     */
    final public function insert(string $table, ModelInterface|array $model): mixed
    {
        if ($model instanceof ModelInterface) {
            $transform = Cluster::transform($model);

            $data = $model = $transform['data'];
            foreach ($data as $key => $value) if (is_null($value)) unset($data[$key]);
            $col = implode(",", array_keys($data));
            $val = '';
            foreach (array_keys($data) as $colValue) {
                if(array_key_exists($colValue, $transform['wrapper']))
                    $val .= sprintf($transform['wrapper'][$colValue], ':' . $colValue) . ',';
                else $val .= ':' . $colValue . ', ';
            }
            $val = rtrim($val, " ,");
        } else {
            $data = $model;
            foreach ($data as $key => $value) if (is_null($value)) unset($data[$key]);
            $col = implode(",", array_keys($data));
            $val = ":".implode(",:", array_keys($data));
        }

        try {
            $query = "INSERT INTO $table ($col) VALUES ($val) RETURNING " . array_key_first($model);
            Log::trace('CDO insert:' . $query);

            $stmt = $this->prepare($query);
            foreach ($data as $keyVal => $paramVal) {
                switch (gettype($paramVal)) {
                    case 'NULL'    :  $stmt->bindValue(':' . $keyVal, $paramVal, PDO::PARAM_NULL); break;
                    case 'boolean' :  $stmt->bindValue(':' . $keyVal, $paramVal, PDO::PARAM_BOOL); break;
                    case 'integer' :  $stmt->bindValue(':' . $keyVal, $paramVal, PDO::PARAM_INT); break;
                    case 'array'   :  $stmt->bindValue(':' . $keyVal, json_encode($paramVal)); break;
                    case 'object'  :  $stmt->bindValue(':' . $keyVal, serialize($paramVal)); break;
                    default: $stmt->bindValue(':' . $keyVal, $paramVal); break;
                }
            }
            $stmt->execute();
            $result = $stmt->fetchColumn();
            if (!$result)
                CDOError::throw(HttpCode::INTERNAL_SERVER_ERROR, 'CDO: Error when creating a record in the database (' . $result . ')');
            return $result;
        } catch (PDOException $ex) {
            CDOError::throw(HttpCode::INTERNAL_SERVER_ERROR, 'CDO: Error when creating a record in the database (' . $ex->getMessage() . ')');
        }
    }

    /**
     * Update an entry in the database
     *
     * @param string $table table name in database
     * @param ModelInterface|array $model data
     * @param BKB $bkb BKBObject
     *
     * @return int|string
     */
    final public function update(string $table, ModelInterface|array $model, BKB $bkb): int|string
    {
        if ($model instanceof ModelInterface) {
            $transform = Cluster::transform($model);
            $data = $transform['data'];
            $set = '';
            foreach ($data as $key => $value) {
                $data[":S_$key"] = $value; unset($data[$key]);
                if(array_key_exists($key, $transform['wrapper']))
                    $set .= ",$key=" . sprintf($transform['wrapper'][$key], ':S_' . $key);
                else $set .= ",$key=:S_$key";
            }
        } else {
            $data = $model;
            $set = "";
            foreach ($data as $key => $value) {
                $data[":S_$key"] = $value; unset($data[$key]);
                $set .= ",$key=:S_$key";
            }
        }

        // Send
        try {
            $query = "UPDATE $table SET ". ltrim($set, ", ") ." WHERE " . $bkb->getQuery();
            Log::trace('CDO update:' . $query);

            $stmt = $this->prepare($query);
            foreach ([...$bkb->getCache(), ...$data] as $keyVal => $paramVal) {
                switch (gettype($paramVal)) {
                    case 'NULL'    :  $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_NULL); break;
                    case 'boolean' :  $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_BOOL); break;
                    case 'integer' :  $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_INT); break;
                    case 'array'   :  $stmt->bindValue($keyVal, json_encode($paramVal)); break;
                    case 'object'  :  $stmt->bindValue($keyVal, serialize($paramVal)); break;
                    default: $stmt->bindValue($keyVal, $paramVal); break;
                }
            }
            $stmt->execute();
            $result = $stmt->rowCount();
            if (!is_numeric($result))
                CDOError::throw(HttpCode::INTERNAL_SERVER_ERROR, 'CDO: Error when changing a record in the database (' . $result . ')');
            return $result;
        } catch (PDOException $ex) {
            CDOError::throw(HttpCode::INTERNAL_SERVER_ERROR, 'CDO: Error when changing a record in the database (' . $ex->getMessage() . ')');
        }
    }

    /**
     * Delete an entry in the database
     *
     * @param string $table table name in database
     * @param BKB $bkb BKBObject
     *
     * @return int|string deleted count
     */
    final public function delete(string $table, BKB $bkb): int|string
    {
        // Send
        try {
            $query = "DELETE FROM $table WHERE " . $bkb->getQuery();
            Log::trace('CDO delete:' . $query);

            $stmt = $this->prepare($query);
            foreach ($bkb->getCache() as $keyVal => $paramVal) {
                switch (gettype($paramVal)) {
                    case 'NULL': $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_NULL); break;
                    case 'boolean': $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_BOOL); break;
                    case 'integer': $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_INT); break;
                    default: $stmt->bindValue($keyVal, $paramVal); break;
                }
            }
            $stmt->execute();
            $result = $stmt->rowCount();
            if (!is_numeric($result))
                CDOError::throw(HttpCode::INTERNAL_SERVER_ERROR, 'CDO: Error deleting a record in the database (' . $result . ')');
            return $result;
        } catch (PDOException $ex) {
            CDOError::throw(HttpCode::INTERNAL_SERVER_ERROR, 'CDO: Error deleting a record in the database (' . $ex->getMessage() . ')');
        }
    }
}
