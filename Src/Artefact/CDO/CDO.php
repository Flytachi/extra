<?php

namespace Extra\Src\Artefact\CDO;

use Extra\Src\Artefact\Mechanism\Shard;
use Extra\Src\HttpCode;
use Extra\Src\Log\Log;
use Extra\Src\Model\ModelInterface;
use Extra\Src\Repo\BKB;
use PDO;
use PDOException;

/**
 * Class CDO
 *
 * The `CDO` class extends the PDO (PHP Data Objects) class
 * and provides methods for handling CRUD (Create, Read, Update, Delete) operations
 * against a database. By extending the PDO class, `CDO` inherits
 * its methods, allowing you to use those methods on a `CDO` object.
 *
 * The constructor of `CDO` attempts to establish a database connection using information
 * from a `Shard` object and optionally enables debug mode. If the connection is
 * successful, it returns a PDO object. If it fails, it throws a PDOException.
 *
 * Additional methods are provided for performing specific database operations:
 *
 * - `insert()`: Inserts a new record into a specified database table
 * - `update()`: Updates an existing record in a specified database table
 * - `delete()`: Deletes a record from a specified database table
 *
 * Note: This class requires a `Shard` object to establish connections, and a BKB (Bucket)
 * object to specify conditions for updating and deleting records.
 *
 * @version 11.4
 * @author Flytachi
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
            $this->setAttribute(PDO::ATTR_PERSISTENT, $shard->getPersistentStatus());
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
        if ($model instanceof ModelInterface) $model = (array) $model;
        $data = $model;
        foreach ($data as $key => $value) if (is_null($value)) unset($data[$key]);
        $col = implode(",", array_keys($data));
        $val = ":".implode(",:", array_keys($data));

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
        $data = (array) $model;
        $set = "";
        foreach ($data as $key => $value) {
            $data[":S_$key"] = $value; unset($data[$key]);
            $set .= ",$key=:S_$key";
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
