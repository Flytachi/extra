<?php

namespace Extra\Src;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\HttpStatus;

/**
 *  Warframe collection
 *
 *  Logger - logger system.
 *  Level types:
 *  * ALL - logging all actions
 *  * ERROR - logging only errors
 *  * WARNING - logging errors and warnings
 *
 * 	@version 2.0
 * 	@author itachi
 * 	@package Extra\Src
 */
class Logger
{
    /**
     * @var false|resource $resource
     */
    private static $resource;

    private static function init(string $fileName): void
    {
        if (!is_dir(PATH_LOG)) mkdir(PATH_LOG);
        if (!is_writable(PATH_LOG)) {
            $status = HttpStatus::message(HttpCode::from(500));
            header("HTTP/1.1 500 " . $status);
            header("Status: 500 " . $status);
            dd("The \"logs\" folder does not have write access");
        }
        self::$resource = fopen(PATH_LOG . '/' . $fileName . '.txt', 'a');
    }

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
     * Logging Api Function
     *
     * The function itself determines which logging level to use
     *
     * @param int $httpCodeValue index http error code
     * @param string $message message logging
     *
     * @return void
     */
    public final static function loggingApi(int $httpCodeValue, string $message): void
    {
        if (LOGGER_LOGGING_LEVEL != 'NONE') {
            $st = (int)($httpCodeValue / 100);
            if (LOGGER_LOGGING_LEVEL == 'ALL') {
                if ($st == 5) self::errorApi($message);
                elseif ($st == 4) self::warningApi($message);
                else self::infoApi($message);
            } elseif (LOGGER_LOGGING_LEVEL == 'WARNING') {
                if ($st == 5) self::errorApi($message);
                elseif ($st == 4) self::warningApi($message);
            } elseif (LOGGER_LOGGING_LEVEL == 'ERROR') {
                if ($st == 5) self::errorApi($message);
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
     * Logging Error Api
     *
     * @param string $message message logging
     *
     * @return void
     */
    public final static function errorApi(string $message): void
    {
        if (self::$resource !== false) {
            self::init('error-api');
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
     * Logging Warning Api
     *
     * @param string $message message logging
     *
     * @return void
     */
    public final static function warningApi(string $message): void
    {
        if (self::$resource !== false) {
            self::init('warning-api');
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

    /**
     * Logging Info Api
     *
     * @param string $message message logging
     *
     * @return void
     */
    public final static function infoApi(string $message): void
    {
        if (self::$resource !== false) {
            self::init('info-api');
            $message = '[' . date('r') . '] | ' . $message . PHP_EOL;
            fwrite(self::$resource, $message);
        }
    }

}