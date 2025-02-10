<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Unit;

use DateInterval;
use DateTime;
use DateTimeZone;

/**
 * Class TimeTool
 *
 * The `TimeTool` for working with date and time.
 *
 * The methods provided by `TimeTool` include:
 *
 * - `isDateTime(string $dataTime): bool`: Checks if a valid date and time.
 * - `dateConvertTo(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string`: Converts a
 * given datetime from a default timezone (UTC) to a destination timezone.
 * - `dateConvertToUTC(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string`: Converts
 * a given datetime from source timezone to UTC.
 * - `now(string $datetime = 'now'): DateTime`: Get the current date and time.
 * - `diff(string $datetime, string $datetimeTarget): DateInterval|false`: Returns the difference
 * between two datetime strings as a DateInterval object.
 * - `interval(string $datetime): DateInterval|false`: Creates a DateInterval object from a given datetime string.
 * - `sleepMic(int $microseconds): void`: sleep microseconds.
 * - `sleepMil(int $milliseconds): void`: sleep milliseconds.
 * - `sleepSec(int $seconds): void`: sleep seconds.
 * - `sleepMin(int $minutes): void`: sleep minutes.
 *
 * @version 2.0
 * @author Flytachi
 */
final class TimeTool
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
            if (trim($dataTime) === '') {
                return false;
            }
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
     * @throws UnitException
     */
    public static function dateConvertTo(string $dataTime, string $timeZone): DateTime
    {
        try {
            $date = new DateTime($dataTime, new DateTimeZone(env('TIME_ZONE', 'UTC')));
            $date->setTimezone(new DateTimeZone($timeZone));
            return $date;
        } catch (\Throwable $exception) {
            throw new UnitException($exception->getMessage(), previous: $exception);
        }
    }

    /**
     * Converts the given date and time to the current timezone.
     *
     * @param string $dataTime The original date and time string to convert.
     * @param string $timeZone The timezone of the original date and time.
     * @return DateTime The converted date and time in the current timezone.
     * @throws UnitException
     */
    public static function dateConvertToCurrent(string $dataTime, string $timeZone): DateTime
    {
        try {
            $date = new DateTime($dataTime, new DateTimeZone($timeZone));
            $date->setTimezone(new DateTimeZone(env('TIME_ZONE', 'UTC')));
            return $date;
        } catch (\Throwable $exception) {
            throw new UnitException($exception->getMessage(), previous: $exception);
        }
    }

    /**
     * Get the current date and time.
     *
     * @param string $datetime The date and time string to be used as the base. Defaults to 'now'.
     * @return DateTime A DateTime object representing the current date and time.
     * @throws UnitException
     */
    final public static function now(string $datetime = 'now'): DateTime
    {
        try {
            return new \DateTime($datetime);
        } catch (\Throwable $exception) {
            throw new UnitException($exception->getMessage(), previous: $exception);
        }
    }

    /**
     * Returns the difference between two datetime strings as a DateInterval object.
     *
     * @param string $datetime The first datetime string.
     * @param string $datetimeTarget The second datetime string.
     *
     * @return DateInterval|false Returns the difference between two datetime strings as a DateInterval object,
     *                          or false if an error occurs during the calculation.
     * @throws UnitException
     */
    final public static function diff(string $datetime, string $datetimeTarget): DateInterval|false
    {
        try {
            return self::now($datetime)->diff(self::now($datetimeTarget));
        } catch (\Throwable $exception) {
            throw new UnitException($exception->getMessage(), previous: $exception);
        }
    }

    /**
     * Creates a DateInterval object from a given datetime string.
     *
     * @param string $datetime The datetime string to create a DateInterval from.
     * @return DateInterval|false The created DateInterval object if successful, false otherwise.
     * @throws UnitException
     */
    final public static function interval(string $datetime): DateInterval|false
    {
        try {
            return DateInterval::createFromDateString($datetime);
        } catch (\Throwable $exception) {
            throw new UnitException($exception->getMessage(), previous: $exception);
        }
    }

    /**
     * Suspends the execution of the current script for the specified number of microseconds.
     *
     * @param int $microseconds The number of microseconds to sleep.
     * @return void
     */
    final public static function sleepMic(int $microseconds): void
    {
        usleep($microseconds);
    }

    /**
     * Sleeps for the given number of milliseconds.
     *
     * @param int $milliseconds The number of milliseconds to sleep.
     * @return void
     */
    final public static function sleepMil(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }

    /**
     * Delays the execution of the script for the specified number of seconds.
     *
     * @param int $seconds The number of seconds to sleep.
     * @return void
     */
    final public static function sleepSec(int $seconds): void
    {
        sleep($seconds);
    }

    /**
     * Sleeps for the given number of minutes.
     *
     * @param int $minutes The number of minutes to sleep.
     * @return void
     */
    final public static function sleepMin(int $minutes): void
    {
        sleep($minutes * 60);
    }
}
