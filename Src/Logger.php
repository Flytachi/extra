<?php

namespace Extra\Src;

use Extra\Src\Log\LoggerBase;
use Extra\Src\Log\LoggerInterface;

/**
 *  Warframe collection
 *
 *  Logger - logger system.
 *  Level types:
 *  * ALL - logging all actions
 *  * ERROR - logging only errors
 *  * WARNING - logging errors and warnings
 *
 * 	@version 3.0
 * 	@author itachi
 * 	@package Extra\Src
 */
class Logger extends LoggerBase implements LoggerInterface
{
    /**
     * Logging Function
     *
     * The function itself determines which logging level to use
     *
     * @param int $httpCodeValue index http error code
     * @param string $message message logging
     *
     * @return void
     */
    public final static function logging(int $httpCodeValue, string $message): void
    {
        if (LOGGER_LOGGING_LEVEL != 'NONE') {
            $st = (int)($httpCodeValue / 100);
            if (LOGGER_LOGGING_LEVEL == 'ALL') {
                if ($st == 5) self::error($message);
                elseif ($st == 4) self::warning($message);
                else self::info($message);
            } elseif (LOGGER_LOGGING_LEVEL == 'WARNING') {
                if ($st == 5) self::error($message);
                elseif ($st == 4) self::warning($message);
            } elseif (LOGGER_LOGGING_LEVEL == 'ERROR') {
                if ($st == 5) self::error($message);
            }
        }
    }

    /**
     * Logging Error
     *
     * @param string $message message logging
     *
     * @return void
     */
    public final static function error(string $message): void
    {
        if (self::$resource !== false) {
            self::init('error');
            $message = '[' . date('r') . '] | ' . $message . PHP_EOL;
            fwrite(self::$resource, $message);
        }
    }

    /**
     * Logging Warning
     *
     * @param string $message message logging
     *
     * @return void
     */
    public final static function warning(string $message): void
    {
        if (self::$resource !== false) {
            self::init('warning');
            $message = '[' . date('r') . '] | ' . $message . PHP_EOL;
            fwrite(self::$resource, $message);
        }
    }

    /**
     * Logging Info
     *
     * @param string $message message logging
     *
     * @return void
     */
    public final static function info(string $message): void
    {
        if (self::$resource !== false) {
            self::init('info');
            $message = '[' . date('r') . '] | ' . $message . PHP_EOL;
            fwrite(self::$resource, $message);
        }
    }
}