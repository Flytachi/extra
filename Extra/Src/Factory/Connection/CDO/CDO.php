<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Connection\CDO;

use Flytachi\Extra\Extra;
use Flytachi\Extra\Src\Factory\Connection\Qb;
use Flytachi\Extra\Src\Factory\Connection\Config\Common\DbConfigInterface;
use Monolog\Logger;
use PDO;
use PDOException;
use stdClass;

class CDO extends PDO
{
    private static Logger $logger;

    /**
     * Constructor
     *
     * @param DbConfigInterface $config
     * @param bool $debug debug mode
     *
     * @return void
     * @throws CDOException
     */
    public function __construct(DbConfigInterface $config, bool $debug = false)
    {
        self::$logger = Extra::$logger->withName("CDO");
        try {
            parent::__construct($config->getDNS(), $config->getUsername(), $config->getPassword());
            $this->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->setAttribute(PDO::ATTR_PERSISTENT, $config->getPersistentStatus());
            if ($debug) {
                $this->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            self::$logger->debug('connection:' . $config->getDns());
        } catch (PDOException $e) {
            throw new CDOException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Create an entry in the database
     *
     * @param string $table table name in database
     * @param object|array $model model or array data
     *
     * @return mixed
     * @throws CDOException
     */
    final public function insert(string $table, object|array $model): mixed
    {
        if (is_object($model)) {
            $model = (array) $model;
        }
        $data = $model;
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                unset($data[$key]);
            }
        }
        $col = implode(",", array_keys($data));
        $val = ":" . implode(",:", array_keys($data));

        try {
            $query = "INSERT INTO $table ($col) VALUES ($val) RETURNING " . array_key_first($model);
            self::$logger->debug('insert:' . $query);

            $stmt = new CDOStatement($this->prepare($query));
            foreach ($data as $keyVal => $paramVal) {
                switch (gettype($paramVal)) {
                    case 'NULL':
                        $stmt->bindValue(':' . $keyVal, $paramVal, PDO::PARAM_NULL);
                        break;
                    case 'boolean':
                        $stmt->bindValue(':' . $keyVal, $paramVal, PDO::PARAM_BOOL);
                        break;
                    case 'integer':
                        $stmt->bindValue(':' . $keyVal, $paramVal, PDO::PARAM_INT);
                        break;
                    case 'array':
                        $stmt->bindValue(':' . $keyVal, json_encode($paramVal));
                        break;
                    case 'object':
//                        if ($paramVal instanceof Type) {
//                            $stmt->updateStm($this->prepare(str_replace(
//                                ':' . $keyVal,
//                                sprintf($paramVal::prepairing(), ':' . $keyVal),
//                                $query
//                            )));
//                            $stmt->bindValue(':' . $keyVal, (string) $paramVal);
//                        } else {
                            $stmt->bindValue(':' . $keyVal, serialize($paramVal));
//                        }
                        break;
                    default:
                        $stmt->bindValue(':' . $keyVal, $paramVal);
                        break;
                }
            }
            $stmt->getStmt()->execute();
            $result = $stmt->getStmt()->fetchColumn();
            if (!$result) {
                throw new CDOException('Error when creating a record in the database (' . $result . ')');
            }
            return $result;
        } catch (PDOException $ex) {
            throw new CDOException('Error when creating a record in the database (' . $ex->getMessage() . ')');
        }
    }

    /**
     * Create an entries in the database
     *
     * @param string $table table name in database
     * @param array<object|array> $models model or array data
     * @throws CDOException
     */
    final public function insertGroup(string $table, object|array ...$models): void
    {
        $data = [];
        $prefix = 0;
        $val = '';
        foreach ($models as $model) {
            if (is_object($model)) {
                $model = (array) $model;
            }
            $items = $model;
            foreach ($items as $key => $value) {
                if (is_null($value)) {
                    unset($items[$key]);
                }
            }
            $col = implode(",", array_keys($items));
            $newKeys = array_map(function ($oldKey) use ($prefix) {
                return $oldKey . '_' . $prefix;
            }, array_keys($items));
            $items = array_combine($newKeys, array_values($items));
            foreach ($items as $key => $value) {
                if (is_null($value)) {
                    unset($items[$key]);
                }
            }
            $val .= '(:' . implode(",:", array_keys($items)) . '),';
            ++$prefix;
            $data = array_merge($data, $items);
        }

        try {
            $query = "INSERT INTO $table ($col) VALUES " . rtrim($val, ',');

            self::$logger->debug('insert group:' . $query);

            $stmt = new CDOStatement($this->prepare($query));
            foreach ($data as $keyVal => $paramVal) {
                switch (gettype($paramVal)) {
                    case 'NULL':
                        $stmt->bindValue(':' . $keyVal, $paramVal, PDO::PARAM_NULL);
                        break;
                    case 'boolean':
                        $stmt->bindValue(':' . $keyVal, $paramVal, PDO::PARAM_BOOL);
                        break;
                    case 'integer':
                        $stmt->bindValue(':' . $keyVal, $paramVal, PDO::PARAM_INT);
                        break;
                    case 'array':
                        $stmt->bindValue(':' . $keyVal, json_encode($paramVal));
                        break;
                    case 'object':
//                        if ($paramVal instanceof Type) {
//                            $stmt->updateStm($this->prepare(str_replace(
//                                ':' . $keyVal,
//                                sprintf($paramVal::prepairing(), ':' . $keyVal),
//                                $query
//                            )));
//                            $stmt->bindValue(':' . $keyVal, (string) $paramVal);
//                        } else {
                            $stmt->bindValue(':' . $keyVal, serialize($paramVal));
//                        }
                        break;
                    default:
                        $stmt->bindValue(':' . $keyVal, $paramVal);
                        break;
                }
            }
            $stmt->getStmt()->execute();
        } catch (PDOException $ex) {
            throw new CDOException('Error when creating a records in the database (' . $ex->getMessage() . ')');
        }
    }

    /**
     * Update an entry in the database
     *
     * @param string $table table name in database
     * @param object|array $model data
     * @param Qb $qb QlObject
     *
     * @return int|string
     * @throws CDOException
     */
    final public function update(string $table, object|array $model, Qb $qb): int|string
    {
        $data = (array) $model;
        $set = "";
        foreach ($data as $key => $value) {
            $data[":S_$key"] = $value;
            unset($data[$key]);
            $set .= ",$key=:S_$key";
        }

        // Send
        try {
            $query = "UPDATE $table SET " . ltrim($set, ", ") . " WHERE " . $qb->getQuery();
            self::$logger->debug('update:' . $query);

            $stmt = new CDOStatement($this->prepare($query));
            foreach ([...$qb->getCache(), ...$data] as $keyVal => $paramVal) {
                switch (gettype($paramVal)) {
                    case 'NULL':
                        $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_NULL);
                        break;
                    case 'boolean':
                        $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_BOOL);
                        break;
                    case 'integer':
                        $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_INT);
                        break;
                    case 'array':
                        $stmt->bindValue($keyVal, json_encode($paramVal));
                        break;
                    case 'object':
                        if ($paramVal instanceof Type) {
                            $stmt->updateStm($this->prepare(str_replace(
                                $keyVal,
                                sprintf($paramVal::prepairing(), $keyVal),
                                $query
                            )));
                            $stmt->bindValue($keyVal, (string) $paramVal);
                        } else {
                            $stmt->bindValue($keyVal, serialize($paramVal));
                        }
                        $stmt->bindValue($keyVal, serialize($paramVal));
                        break;
                    default:
                        $stmt->bindValue($keyVal, $paramVal);
                        break;
                }
            }
            $stmt->getStmt()->execute();
            $result = $stmt->getStmt()->rowCount();
            if (!is_numeric($result)) {
                throw new CDOException('Error when changing a record in the database (' . $result . ')');
            }
            return $result;
        } catch (PDOException $ex) {
            throw new CDOException('Error when changing a record in the database (' . $ex->getMessage() . ')');
        }
    }

    /**
     * Delete an entry in the database
     *
     * @param string $table table name in database
     * @param Qb $qb QlObject
     *
     * @return int|string deleted count
     * @throws CDOException
     */
    final public function delete(string $table, Qb $qb): int|string
    {
        // Send
        try {
            $query = "DELETE FROM $table WHERE " . $qb->getQuery();
            self::$logger->debug('delete:' . $query);

            $stmt = $this->prepare($query);
            foreach ($qb->getCache() as $keyVal => $paramVal) {
                switch (gettype($paramVal)) {
                    case 'NULL':
                        $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_NULL);
                        break;
                    case 'boolean':
                        $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_BOOL);
                        break;
                    case 'integer':
                        $stmt->bindValue($keyVal, $paramVal, PDO::PARAM_INT);
                        break;
                    default:
                        $stmt->bindValue($keyVal, $paramVal);
                        break;
                }
            }
            $stmt->execute();
            $result = $stmt->rowCount();
            if (!is_numeric($result)) {
                throw new CDOException('Error deleting a record in the database (' . $result . ')');
            }
            return $result;
        } catch (PDOException $ex) {
            throw new CDOException('Error deleting a record in the database (' . $ex->getMessage() . ')');
        }
    }
}
