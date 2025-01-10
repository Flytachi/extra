<?php

namespace Extra\Src\Repo;

use Extra\Src\Sheath\Algorithm;

/**
 * Class BKB
 *
 * `BKB` is utility a class that generates and holds SQL conditions.
 * When constructing this class, it can generate various comparison operators for constructing SQL conditions.
 *
 * Additionally, the class provides the method:
 *
 * - `getData()`: Returns the prepared SQL condition query and the cache, which includes the parameters for the query.
 * - `getQuery()`: Returns the prepared SQL condition query.
 * - `getCache()`: Returns the parameters that were constructed for the condition.
 *
 * And static methods to build condition "clauses" for the prepared statement:
 *
 * - `eq(string $column, int|float|string $value): BKB`: Equal to operator.
 * - `neq(string $column, int|float|string $value): BKB`: Not equal to operator.
 * - `gt(string $column, int|float|string $value): BKB`: Greater than operator.
 * - `geq(string $column, int|float|string $value): BKB`: Greater than or equal to operator.
 * - `lt(string $column, int|float|string $value): BKB`: Less than operator.
 * - `leq(string $column, int|float|string $value): BKB`: Less than or equal to operator.
 * - `nsEq(string $column, int|float|string $value): BKB`: NULL-safe equal to operator.
 *
 * @version 3.5
 * @author Flytachi
 */
class BKB
{
    private string $prepareData;
    private array $cache;

    private function __construct(string $temp, array $cache)
    {
        $this->prepareData = $temp;
        $this->cache = $cache;
    }

    /**
     * Return Data
     *
     * @return array
     */
    public function getData(): array
    {
        return [
            'query' => $this->prepareData,
            'cache' => $this->cache
        ];
    }

    /**
     * Return Query Data
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->prepareData;
    }

    /**
     * Return Cache Data
     *
     * @return array
     */
    public function getCache(): array
    {
        return $this->cache;
    }

    /**
     * Equal operator
     *
     * Parsed: $column = $value
     *
     * @param string $column name of the column in the table
     * @param int|float|string $value value
     *
     * @return BKB
     */
    public static function eq(string $column, int|float|string $value): BKB
    {
        $hash = self::inject($value);
        return new self("{$column} = {$hash}", [$hash => $value]);
    }

    /**
     * Not equal operator
     *
     * Parsed: $column != $value
     *
     * @param string $column name of the column in the table
     * @param int|float|string $value value
     *
     * @return BKB
     */
    public static function neq(string $column, int|float|string $value): BKB
    {
        $hash = self::inject($value);
        return new self("{$column} != {$hash}", [$hash => $value]);
    }

    /**
     * Greater than operator
     *
     * Parsed: $column > $value
     *
     * @param string $column name of the column in the table
     * @param int|float|string $value value
     *
     * @return BKB
     */
    public static function gt(string $column, int|float|string $value): BKB
    {
        $hash = self::inject($value);
        return new self("{$column} > {$hash}", [$hash => $value]);
    }

    /**
     * Greater than or equal operator
     *
     * Parsed: $column >= $value
     *
     * @param string $column name of the column in the table
     * @param int|float|string $value value
     *
     * @return BKB
     */
    public static function geq(string $column, int|float|string $value): BKB
    {
        $hash = self::inject($value);
        return new self("{$column} >= {$hash}", [$hash => $value]);
    }

    /**
     * Less than operator
     *
     * Parsed: $column < $value
     *
     * @param string $column name of the column in the table
     * @param int|float|string $value value
     *
     * @return BKB
     */
    public static function lt(string $column, int|float|string $value): BKB
    {
        $hash = self::inject($value);
        return new self("{$column} < {$hash}", [$hash => $value]);
    }

    /**
     * Less than or equal operator
     *
     * Parsed: $column <= $value
     *
     * @param string $column name of the column in the table
     * @param int|float|string $value value
     *
     * @return BKB
     */
    public static function leq(string $column, int|float|string $value): BKB
    {
        $hash = self::inject($value);
        return new self("{$column} <= {$hash}", [$hash => $value]);
    }

    /**
     * NULL-safe equal to operator
     *
     * Parsed: $column <=> $value
     *
     * Note: NULL-safe equal. This operator performs an equality comparison
     * like the = operator, but returns 1 rather than NULL if both
     * operands are NULL, and 0 rather than NULL if one operand is NULL.
     *
     * @param string $column name of the column in the table
     * @param int|float|string $value value
     *
     * @return BKB
     */
    public static function nsEq(string $column, int|float|string $value): BKB
    {
        $hash = self::inject($value);
        return new self("{$column} <=> {$hash}", [$hash => $value]);
    }

    /**
     * NULL value test
     *
     * Parsed: $column IS NULL
     *
     * Note: Tests whether a value is NULL.
     *
     * @param string $column name of the column in the table
     *
     * @return BKB
     */
    public static function isNull(string $column): BKB
    {
        return new self("{$column} IS NULL", []);
    }

    /**
     * NOT NULL value test
     *
     * Parsed: $column IS NOT NULL
     *
     * Note: Tests whether a value is not NULL.
     *
     * @param string $column name of the column in the table
     *
     * @return BKB
     */
    public static function isNotNull(string $column): BKB
    {
        return new self("{$column} IS NOT NULL", []);
    }

    /**
     * Change value boolean (true)
     *
     * Parsed: $column IS TRUE
     *
     * @param string $column name of the column in the table
     *
     * @return BKB
     */
    public static function isTrue(string $column): BKB
    {
        return new self("{$column} IS TRUE", []);
    }

    /**
     * Change value boolean (false)
     *
     * Parsed: $column IS FALSE
     *
     * @param string $column name of the column in the table
     *
     * @return BKB
     */
    public static function isFalse(string $column): BKB
    {
        return new self("{$column} IS FALSE", []);
    }

    /**
     * Whether a value is within a set of values
     *
     * Parsed: $column IN (value, ...)
     *
     * @param string $column name of the column in the table
     * @param array $array list values
     *
     * @return BKB
     */
    public static function in(string $column, array $array): BKB
    {
        $data = self::prepareIn($array);
        return new self("{$column} IN ({$data['prepareData']})", $data['cache']);
    }

    /**
     * Whether a value is not within a set of values
     *
     * Parsed: $column NOT IN (value, ...)
     *
     * @param string $column name of the column in the table
     * @param array $array list values
     *
     * @return BKB
     */
    public static function inNot(string $column, array $array): BKB
    {
        $data = self::prepareIn($array);
        return new self("{$column} NOT IN ({$data['prepareData']})", $data['cache']);
    }

    /**
     * Simple pattern matching
     *
     * Parsed: $column LIKE $value
     *
     * Note: Pattern matching using an SQL pattern. Returns 1 (TRUE)
     * or 0 (FALSE). If either expr or pat is NULL, the result is NULL.
     *
     * @param string $column name of the column in the table
     * @param string $value string value
     *
     * @return BKB
     */
    public static function like(string $column, string $value): BKB
    {
        $hash = self::inject($value);
        return new self("{$column} LIKE {$hash}", [$hash => $value]);
    }

    /**
     * Negation of simple pattern matching
     *
     * Parsed: $column NOT LIKE $value
     *
     * @param string $column name of the column in the table
     * @param string $value string value
     *
     * @return BKB
     */
    public static function likeNot(string $column, string $value): BKB
    {
        $hash = self::inject($value);
        return new self("{$column} NOT LIKE {$hash}", [$hash => $value]);
    }

    /**
     * BETWEEN operator
     *
     * Parsed: $column BETWEEN $valueMin AND $valueMax
     *
     * Note: The BETWEEN operator is used to select values within a range.
     * The values can be numbers, text, or dates.
     *
     * @param string $column name of the column in the table
     * @param int|float|string $valueMin - minimum value
     * @param int|float|string $valueMax - maximum value
     *
     * @return BKB
     */
    public static function between(string $column, string|int|float $valueMin, string|int|float $valueMax): BKB
    {
        $hashMin = self::inject($valueMin);
        $hashMax = self::inject($valueMax);
        return new self("{$column} BETWEEN {$hashMin} AND {$hashMax}", [
            $hashMin => $valueMin,
            $hashMax => $valueMax,
        ]);
    }

    /**
     * BETWEEN operator with column values
     *
     * Parsed: $value BETWEEN $column1 AND $column2
     *
     * Note: The BETWEEN operator is used to search for values that are within a specified range.
     * The values can be numbers or dates.
     *
     * @param string|int|float $value value to be compared
     * @param string $column1 name of the first column in the table
     * @param string $column2 name of the second column in the table
     *
     * @return BKB
     */
    public static function betweenBy(string|int|float $value, string $column1, string $column2): BKB
    {
        $hash = self::inject($value);
        return new self("{$value} BETWEEN {$column1} AND {$column2}", [
            $hash => $value,
        ]);
    }

    /**
     * NOT between operator
     *
     * Parsed: $column NOT BETWEEN $valueMin AND $valueMax
     *
     * Note: This operator returns true if the operand on the left
     * is NOT within the range of the operands on the right.
     *
     * @param string $column name of the column in the table
     * @param int|float|string $valueMin minimum value
     * @param int|float|string $valueMax maximum value
     *
     * @return BKB
     */
    public static function betweenNot(string $column, string|int|float $valueMin, string|int|float $valueMax): BKB
    {
        $hashMin = self::inject($valueMin);
        $hashMax = self::inject($valueMax);
        return new self("{$column} NOT BETWEEN {$hashMin} AND {$hashMax}", [
            $hashMin => $valueMin,
            $hashMax => $valueMax,
        ]);
    }

    /**
     * NOT-BETWEEN operator
     *
     * Parsed: $value NOT BETWEEN $column1 AND $column2
     *
     * Note: The NOT-BETWEEN operator checks whether a value is not within a specified range.
     *
     * @param string|int|float $value The value to check if not between the columns
     * @param string $column1 The first column for the comparison
     * @param string $column2 The second column for the comparison
     *
     * @return BKB
     */
    public static function betweenNotBy(string|int|float $value, string $column1, string $column2): BKB
    {
        $hash = self::inject($value);
        return new self("{$value} NOT BETWEEN {$column1} AND {$column2}", [
            $hash => $value,
        ]);
    }

    /**
     * Logical AND operator
     *
     * Parsed: $BKBObject[0] AND $BKBObject[1] AND ...$BKBObject[n]
     *
     * @param null|BKB ...$BKBObjects bkb objects
     *
     * @return BKB
     */
    public static function and(?BKB ...$BKBObjects): BKB
    {
        $data = self::logicalPrepare('AND', $BKBObjects);
        return new self($data['prepareData'], $data['cache']);
    }

    /**
     * Logical OR operator
     *
     * Parsed: $BKBObject[0] OR $BKBObject[1] OR ...$BKBObject[n]
     *
     * @param BKB ...$BKBObjects bkb objects
     *
     * @return BKB
     */
    public static function or(?BKB ...$BKBObjects): BKB
    {
        $data = self::logicalPrepare('OR', $BKBObjects);
        return new self($data['prepareData'], $data['cache']);
    }

    /**
     * Logical XOR operator
     *
     * Parsed: $BKBObject[0] XOR $BKBObject[1] XOR ...$BKBObject[n]
     *
     * @param BKB ...$BKBObjects bkb objects
     *
     * @return BKB
     */
    public static function xor(?BKB ...$BKBObjects): BKB
    {
        $data = self::logicalPrepare('XOR', $BKBObjects);
        return new self($data['prepareData'], $data['cache']);
    }

    /**
     * Custom operator CLIP
     *
     * Parsed: ($BKBObject)
     *
     * @param BKB $BKBObject bkb object
     *
     * @return BKB
     */
    public static function clip(BKB $BKBObject): BKB
    {
        return new self('(' . $BKBObject->prepareData . ')', $BKBObject->cache);
    }

    /**
     * Custom script
     *
     * Warning: Attention be careful! SQL-injection
     * protection will not work in this script
     *
     * @param string $query string
     *
     * @return BKB
     */
    public static function custom(string $query): BKB
    {
        return new self($query, []);
    }

    /**
     * Empty BKB
     *
     * @return BKB
     */
    public static function empty(): BKB
    {
        return new self('', []);
    }

    private static function logicalPrepare(string $prefix, array $BKBObjects): array
    {
        $prefix = " {$prefix} ";
        $value = "";
        $cache = [];
        foreach ($BKBObjects as $BKBObject) {
            if (is_null($BKBObject)) continue;
            if ($BKBObject->prepareData == '') continue;
            $cache = [...$cache, ...$BKBObject->cache];
            $value .= $BKBObject->prepareData . $prefix;
        }

        return [
            'prepareData' => rtrim($value, $prefix),
            'cache' => $cache
        ];
    }
    private static function prepareIn(array $arrayItems): array
    {
        $value = "";
        $cache = [];

        foreach ($arrayItems as $item) {
            $hash = self::inject($item);
            $cache = [...$cache, $hash =>$item];
            $value .= $hash . ',';
        }

        return [
            'prepareData' => rtrim($value, ','),
            'cache' => $cache
        ];
    }

    private static function inject(string|int|float $value): string
    {
        return ':' . Algorithm::random(10, "abcdefghijklmnopqrstuvwxyz0123456789");
    }
}