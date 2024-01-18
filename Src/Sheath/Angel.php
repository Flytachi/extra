<?php

namespace Extra\Src\Sheath;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class Angel
 *
 * The `Angel` class provides a set of utility functions, majorly handling data conversions, type validations and data mappings.
 *
 * The methods provided by `Angel` include:
 *
 * - `dateConvertTo(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string`: Converts a given datetime from a default timezone (UTC) to a destination timezone.
 * - `dateConvertToUTC(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string`: Converts a given datetime from source timezone to UTC.
 * - `isIntPositive(mixed $value): bool`: Checks if a value is positive integer.
 * - `transKirilToLatin(string $strKiril): string`: Transliterates a Cyrillic string to a Latin string.
 * - `mapArrayByColumnValue(array $array, string|int $column): array`: Maps an array by a specified column value.
 *
 * @version 1.0
 * @author Flytachi
 */
class Angel
{
    /**
     * Converts a given date and time to the specified time zone and format.
     *
     * @param string $dataTime The date and time to be converted. Should be in a format supported by the DateTime class.
     * @param string $timeZone The time zone to which the date and time should be converted. Should be a valid time zone supported by the DateTimeZone class.
     * @param string $format Optional. The format to apply to the converted date and time. Defaults to 'Y-m-d H:i:s'.
     *
     * @return string The converted date and time in the specified format.
     */
    public static function dateConvertTo(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            $date = new DateTime($dataTime, new DateTimeZone(env('TIME_ZONE', 'UTC')));
            $date->setTimezone(new DateTimeZone($timeZone));
            return $date->format($format);
        } catch (Exception $e) {
            if (env('DEBUG', false)) dd($e);
            else return '';
        }
    }

    /**
     * Convert a date-time to UTC timezone and format it.
     *
     * @param string $dataTime The date-time to be converted. Must be in a valid format.
     * @param string $timeZone The timezone of the input date-time.
     * @param string $format The desired format of the output date-time. Default is 'Y-m-d H:i:s'.
     *
     * @return string The converted date-time in the specified format. If an error occurs during conversion and the environment variable DEBUG is set to true, the error will be dumped
     * and an empty string will be returned otherwise.
     */
    public static function dateConvertToUTC(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            $date = new DateTime($dataTime, new DateTimeZone($timeZone));
            $date->setTimezone(new DateTimeZone(env('TIME_ZONE', 'UTC')));
            return $date->format($format);
        } catch (Exception $e) {
            if (env('DEBUG', false)) dd($e);
            else return '';
        }
    }

    /**
     * Check if the given value is a positive integer.
     *
     * @param mixed $value The value to be checked.
     * @return bool Returns true if the value is a positive integer, false otherwise.
     */
    public static function isIntPositive(mixed $value): bool
    {
        if (!is_numeric($value)) return false;
        if ((int) $value > 0) return true;
        else return false;
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
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Ё"=>"Yo","ё"=>"yo",
            "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
            "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
            "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
        ]);
    }

    /**
     * Maps an array based on the values of a specified column.
     *
     * @param array $array The array to be mapped.
     * @param string|int $column The key or index of the column to be used.
     *                          If it is a string, the value will be extracted from the associative array using this key.
     *                          If it is an integer, the value will be extracted from the indexed array using this index.
     *
     * @return array The mapped array, where the keys are the values from the specified column and the values are the original array elements.
     */
    public static function mapArrayByColumnValue(array $array, string|int $column): array
    {
        $keys = array_column($array, $column);
        return array_combine($keys, $array);
    }
}
