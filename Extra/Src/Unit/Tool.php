<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Unit;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class Tool
 *
 * The `Tool` class provides a set of utility functions, majorly
 * handling data conversions, type validations and data mappings.
 *
 * The methods provided by `Tool` include:
 *
 * - `isIntPositive(mixed $value): bool`: Checks if a value is positive integer.
 * - `transKirilToLatin(string $strKiril): string`: Transliterates a Cyrillic string to a Latin string.
 * - `mapArrayByColumnValue(array $array, string|int $column): array`: Maps an array by a specified column value.
 *
 * @version 2.0
 * @author Flytachi
 */
final class Tool
{
    /**
     * Determines if a given value is a valid URL.
     *
     * @param mixed $value The value to check if it is a valid URL.
     * @return bool True if the given value is a valid URL, false otherwise.
     */
    public static function isUrl(mixed $value): bool
    {
        return (bool) filter_var($value, FILTER_VALIDATE_URL);
    }

    /**
     * Checks if a given value is a valid email address.
     *
     * @param mixed $value The value to check if it is a valid email address.
     * @return bool True if the given value is a valid email address, false otherwise.
     */
    public static function isEmail(mixed $value): bool
    {
        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Check if the given value is a positive integer.
     *
     * @param mixed $value The value to be checked.
     * @return bool Returns true if the value is a positive integer, false otherwise.
     */
    public static function isIntPositive(mixed $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        if ((int) $value > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Translates a string from Cyrillic to Latin characters.
     *
     * @param string $strCyrillic The string to be translated.
     *
     * @return string The translated string.
     */
    public static function transCyrillicToLatin(string $strCyrillic): string
    {
        return strtr($strCyrillic, [
            "А" => "A","Б" => "B","В" => "V","Г" => "G","Ё" => "Yo","ё" => "yo","Д" => "D",
            "Е" => "E","Ж" => "J","З" => "Z","И" => "I","Й" => "Y","К" => "K","Л" => "L",
            "М" => "M","Н" => "N", "О" => "O","П" => "P","Р" => "R","С" => "S","Т" => "T",
            "У" => "U","Ф" => "F","Х" => "H","Ц" => "TS","Ч" => "CH", "Ш" => "SH","Щ" => "SCH",
            "Ъ" => "","Ы" => "YI","Ь" => "","Э" => "E","Ю" => "YU","Я" => "YA","а" => "a",
            "б" => "b","в" => "v","г" => "g","д" => "d","е" => "e","ж" => "j","з" => "z",
            "и" => "i","й" => "y","к" => "k","л" => "l","м" => "m","н" => "n","о" => "o",
            "п" => "p","р" => "r","с" => "s","т" => "t","у" => "u","ф" => "f","х" => "h",
            "ц" => "ts","ч" => "ch","ш" => "sh","щ" => "sch","ъ" => "y","ы" => "yi","ь" => "",
            "э" => "e","ю" => "yu","я" => "ya"
        ]);
    }

    /**
     * Maps an array based on the values of a specified column.
     *
     * @param array $array The array to be mapped.
     * @param string|int $column The key or index of the column to be used.
     *  If it is a string, the value will be extracted from the associative array using this key.
     *  If it is an integer, the value will be extracted from the indexed array using this index.
     *
     * @return array The mapped array, where the keys are the values from the specified
     * column and the values are the original array elements.
     */
    public static function mapArrayByColumnValue(array $array, string|int $column): array
    {
        $keys = array_column($array, $column);
        return array_combine($keys, $array);
    }


    /**
     * Sorts an array of rows based on given sorting criteria.
     *
     * @param array $data The array of rows to be sorted. This array will be modified.
     * @param array<string, string> $sorts An associative array containing the sorting criteria.
     *                     The keys are the field names and the values are the sorting direction ('asc' or 'desc').
     *
     * @return void
     */
    public static function arraySort(array &$data, array $sorts): void
    {
        $args = [];
        foreach ($sorts as $field => $direction) {
            $col = array_column($data, $field);
            $args[] = $col;
            if ('desc' === $direction) {
                $args[] = SORT_DESC;
            } else {
                $args[] = SORT_ASC;
            }
        }
        $args[] = &$data;
        call_user_func_array("array_multisort", $args);
    }

    /**
     * Calculates the factorial of a given value.
     *
     * @param int $value The value for which to calculate the factorial.
     * @return int The factorial of the given value.
     */
    public static function factorial(int $value): int
    {
        return $value < 2 ? 1
            : $value * self::factorial($value - 1);
    }
}
