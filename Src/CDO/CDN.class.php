<?php

namespace Extra\Src\CDO;

use Extra\Src\RandomGenerator;

/**
 *  Warframe collection
 *
 *  CDN - CDO command helper
 *
 *  @version 1.2
 *  @author itachi
 *  @package Extra\Src
 */
class CDN
{
    private static string $alphabet = "abcdefghijklmnopqrstuvwxyz0123456789";
    private static ?RandomGenerator $generator = null;
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
     * @return CDN
     */
    public static function eq(string $column, int|float|string $value): CDN
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
     * @return CDN
     */
    public static function neq(string $column, int|float|string $value): CDN
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
     * @return CDN
     */
    public static function gt(string $column, int|float|string $value): CDN
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
     * @return CDN
     */
    public static function geq(string $column, int|float|string $value): CDN
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
     * @return CDN
     */
    public static function lt(string $column, int|float|string $value): CDN
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
     * @return CDN
     */
    public static function leq(string $column, int|float|string $value): CDN
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
     * @return CDN
     */
    public static function nsEq(string $column, int|float|string $value): CDN
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
     * @return CDN
     */
    public static function isNull(string $column): CDN
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
     * @return CDN
     */
    public static function isNotNull(string $column): CDN
    {
        return new self("{$column} IS NOT NULL", []);
    }

    /**
     * Whether a value is within a set of values
     *
     * Parsed: $column IN (value, ...)
     *
     * @param string $column name of the column in the table
     * @param array $array list values
     *
     * @return CDN
     */
    public static function in(string $column, array $array): CDN
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
     * @return CDN
     */
    public static function inNot(string $column, array $array): CDN
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
     * @return CDN
     */
    public static function like(string $column, string $value): CDN
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
     * @return CDN
     */
    public static function likeNot(string $column, string $value): CDN
    {
        $hash = self::inject($value);
        return new self("{$column} NOT LIKE {$hash}", [$hash => $value]);
    }

    /**
     * Whether a value is within a range of values
     *
     * Parsed: $column BETWEEN $valueMin AND $valueMax
     *
     * @param string $column name of the column in the table
     * @param string|int|float $valueMin min value
     * @param string|int|float $valueMax max value
     *
     * @return CDN
     */
    public static function between(string $column, string|int|float $valueMin, string|int|float $valueMax): CDN
    {
        $hashMin = self::inject($valueMin);
        $hashMax = self::inject($valueMax);
        return new self("{$column} BETWEEN {$hashMin} AND {$hashMax}", [
            $hashMin => $valueMin,
            $hashMax => $valueMax,
        ]);
    }

    /**
     * Whether a value is not within a range of values
     *
     * Parsed: $column NOT BETWEEN $valueMin AND $valueMax
     *
     * @param string $column name of the column in the table
     * @param string|int|float $valueMin min value
     * @param string|int|float $valueMax max value
     *
     * @return CDN
     */
    public static function betweenNot(string $column, string|int|float $valueMin, string|int|float $valueMax): CDN
    {
        $hashMin = self::inject($valueMin);
        $hashMax = self::inject($valueMax);
        return new self("{$column} NOT BETWEEN {$hashMin} AND {$hashMax}", [
            $hashMin => $valueMin,
            $hashMax => $valueMax,
        ]);
    }

    /**
     * Logical AND operator
     *
     * Parsed: $CDNObject[0] AND $CDNObject[1] AND ...$CDNObject[n]
     *
     * @param CDN ...$CDNObjects cdn objects
     *
     * @return CDN
     */
    public static function and(CDN ...$CDNObjects): CDN
    {
        $data = self::logicalPrepare('AND', $CDNObjects);
        return new self($data['prepareData'], $data['cache']);
    }

    /**
     * Logical OR operator
     *
     * Parsed: $CDNObject[0] OR $CDNObject[1] OR ...$CDNObject[n]
     *
     * @param CDN ...$CDNObjects cdn objects
     *
     * @return CDN
     */
    public static function or(CDN ...$CDNObjects): CDN
    {
        $data = self::logicalPrepare('OR', $CDNObjects);
        return new self($data['prepareData'], $data['cache']);
    }

    /**
     * Logical XOR operator
     *
     * Parsed: $CDNObject[0] XOR $CDNObject[1] XOR ...$CDNObject[n]
     *
     * @param CDN ...$CDNObjects cdn objects
     *
     * @return CDN
     */
    public static function xor(CDN ...$CDNObjects): CDN
    {
        $data = self::logicalPrepare('XOR', $CDNObjects);
        return new self($data['prepareData'], $data['cache']);
    }

    /**
     * Custom operator CLIP
     *
     * Parsed: ($CDNObject)
     *
     * @param CDN $CDNObject cdn object
     *
     * @return CDN
     */
    public static function clip(CDN $CDNObject): CDN
    {
        return new self('(' . $CDNObject->prepareData . ')', $CDNObject->cache);
    }

    /**
     * Custom script
     *
     * Warning: Attention be careful! SQL-injection
     * protection will not work in this script
     *
     * @param string $query string
     *
     * @return CDN
     */
    public static function custom(string $query): CDN
    {
        return new self($query, []);
    }

    /**
     * Empty CDN
     *
     * @return CDN
     */
    public static function empty(): CDN
    {
        return new self('', []);
    }

    private static function logicalPrepare(string $prefix, array $CDNObjects): array
    {
        $prefix = " {$prefix} ";
        $value = "";
        $cache = [];
        foreach ($CDNObjects as $CDNObject) {
            if ($CDNObject->prepareData == '') continue;
            $cache = [...$cache, ...$CDNObject->cache];
            $value .= $CDNObject->prepareData . $prefix;
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
        if(is_null(self::$generator))
            self::$generator = new RandomGenerator(self::$alphabet);
        return ':' . self::$generator->generate(10);
    }
}