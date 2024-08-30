<?php

namespace Extra\Src\Repo\Dependencies;

use Extra\Src\Error\Error;
use Extra\Src\HttpCode;
use Extra\Src\Repo\BKB;

trait FindTrait
{
    /**
     * Finds a record by its ID.
     *
     * @param int|string $id The ID of the record to find.
     * @param string|null $modelClassName The class name of the model to use for the find operation. Defaults to null.
     *
     * @return mixed    Returns the found record if it exists, or null if it does not.
     */
    final public static function findById(int|string $id, ?string $modelClassName = null): mixed
    {
        return (new self)
            ->where(BKB::eq('id', $id))
            ->find($modelClassName);
    }

    /**
     * Finds a record by its ID or throws an error if the record is not found.
     *
     * @param int|string $id The ID of the record to find.
     * @param string|null $modelClassName The class name of the model to use for the find operation. Defaults to null.
     * @param string $message The error message to be thrown if the record is not found. Defaults to 'Object not found'.
     *
     * @return mixed Returns the found record if it exists.
     */
    final public static function findByIdOrThrow(int|string $id, ?string $modelClassName = null, string $message = 'Object not found'): mixed
    {
        $obj = self::findById($id);
        if (!$obj) Error::throw(HttpCode::NOT_FOUND, $message);
        return $obj;
    }

    /**
     * Finds records based on a BKB object.
     *
     * @param BKB $bkb The BKB object containing the conditions for the find operation.
     * @param string|null $modelClassName The class name of the model to use for the find operation. Defaults to null.
     *
     * @return mixed    Returns the found records if any exist, or null if none are found.
     */
    final public static function findBy(BKB $bkb, ?string $modelClassName = null): mixed
    {
        return (new self)->where($bkb)->find($modelClassName);
    }

    /**
     * Finds a record using the provided BKB object and throws an error if the record does not exist.
     *
     * @param BKB $bkb The BKB object used to search for the record.
     * @param string|null $modelClassName The class name of the model to use for the find operation. Defaults to null.
     * @param string $message The error message to throw if the record is not found. Defaults to 'Object not found'.
     *
     * @return mixed Returns the found record if it exists, or throws
     */
    final public static function findByOrThrow(BKB $bkb, ?string $modelClassName = null, string $message = 'Object not found'): mixed
    {
        $obj = self::findBy($bkb);
        if (!$obj) Error::throw(HttpCode::NOT_FOUND, $message);
        return $obj;
    }

    /**
     * Finds multiple records based on a set of conditions.
     *
     * @param null|BKB $bkb The conditions to use for finding the records. Defaults to null.
     * @param string|null $modelClassName The class name of the model to use for the find operation. Defaults to null.
     *
     * @return array|false    Returns an array of found records if they exist, or false if no records are found.
     */
    final public static function findAllBy(?BKB $bkb = null, ?string $modelClassName = null): array|false
    {
        return (new self)->where($bkb)->findAll($modelClassName);
    }
}