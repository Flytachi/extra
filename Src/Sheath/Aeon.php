<?php

namespace Extra\Src\Sheath;

use DateTime;
use DateTimeZone;
use Extra\Src\HttpCode;

/**
 * Class Aeon
 *
 * The `Aeon` for working with date and time.
 *
 * The methods provided by `Aeon` include:
 *
 * - `isDateTime(string $dataTime): bool`: Checks if a valid date and time.
 * - `dateConvertTo(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string`: Converts a given datetime from a default timezone (UTC) to a destination timezone.
 * - `dateConvertToUTC(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string`: Converts a given datetime from source timezone to UTC.
 * - `now(string $datetime = 'now'): DateTime`: Get the current date and time.
 * - `sleepMl(int $microseconds): void`: sleep microseconds.
 * - `sleepSc(int $seconds): void`: sleep seconds.
 * - `sleepMn(int $minutes): void`: sleep minutes.
 *
 * @version 1.0
 * @author Flytachi
 */
abstract class Aeon
{
    /**
     * Checks whether the given string is a valid date and time.
     *
     * @param string $dataTime The string to check for validity as a date and time.
     * @return bool Returns true if the string is a valid date and time, false otherwise.
     */
    public static function isDateTime(string $dataTime): bool
    {
        try {
            new DateTime($dataTime);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Converts the given date and time to the specified timezone.
     *
     * @param string $dataTime The original date and time string to convert.
     * @param string $timeZone The timezone to convert the date and time to.
     * @return DateTime The converted date and time in the specified timezone.
     */
    public static function dateConvertTo(string $dataTime, string $timeZone): \DateTime
    {
        try {
            $date = new DateTime($dataTime, new DateTimeZone(env('TIME_ZONE', 'UTC')));
            $date->setTimezone(new DateTimeZone($timeZone));
            return $date;
        } catch (\Throwable $exception) {
            SheathException::throw(HttpCode::INTERNAL_SERVER_ERROR, 'Aeon: ' . $exception->getMessage());
        }
    }

    /**
     * Converts the given date and time to the current timezone.
     *
     * @param string $dataTime The original date and time string to convert.
     * @param string $timeZone The timezone of the original date and time.
     * @return DateTime The converted date and time in the current timezone.
     */
    public static function dateConvertToCurrent(string $dataTime, string $timeZone): \DateTime
    {
        try {
            $date = new DateTime($dataTime, new DateTimeZone($timeZone));
            $date->setTimezone(new DateTimeZone(env('TIME_ZONE', 'UTC')));
            return $date;
        } catch (\Throwable $exception) {
            SheathException::throw(HttpCode::INTERNAL_SERVER_ERROR, 'Aeon: ' . $exception->getMessage());
        }
    }

    /**
     * Get the current date and time.
     *
     * @param string $datetime The date and time string to be used as the base. Defaults to 'now'.
     * @return DateTime A DateTime object representing the current date and time.
     */
    public final static function now(string $datetime = 'now'): \DateTime
    {
        try {
            return new \DateTime($datetime);
        } catch (\Throwable $exception) {
            SheathException::throw(HttpCode::INTERNAL_SERVER_ERROR, 'Aeon: ' . $exception->getMessage());
        }
    }

    /**
     * Suspends the execution of the current script for the specified number of microseconds.
     *
     * @param int $microseconds The number of microseconds to sleep.
     * @return void
     */
    public final static function sleepMl(int $microseconds): void
    {
        usleep($microseconds);
    }

    /**
     * Delays the execution of the script for the specified number of seconds.
     *
     * @param int $seconds The number of seconds to sleep.
     * @return void
     */
    public final static function sleepSc(int $seconds): void
    {
        sleep($seconds);
    }

    /**
     * Sleeps for the given number of minutes.
     *
     * @param int $minutes The number of minutes to sleep.
     * @return void
     */
    public final static function sleepMn(int $minutes): void
    {
        sleep($minutes*60);
    }
}