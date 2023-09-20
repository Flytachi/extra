<?php

namespace Extra\Src\Log;

class Log extends LoggerBase implements LoggerInterface
{
    protected static string $handle = 'frame';

    /**
     * Trace Log
     *
     * Allowed level: 2
     * Debug: off/on
     *
     * @param string $message
     * @return void
     */
    public static function trace(string $message): void
    {
        self::writeIsLevel(sprintf("[%s] %s | %s",
            date(self::$dateFormat),
            '[TRACE]',
            $message . PHP_EOL
        ), 2);
    }

    /**
     * Debug Log
     *
     * Allowed level: 1,2
     * Debug: on
     *
     * @param string $message
     * @return void
     */
    public static function debug(string $message): void
    {
        if (env('DEBUG', false)) {
            self::write(sprintf("[%s] %s | %s",
                date(self::$dateFormat),
                '[DEBUG]',
                $message . PHP_EOL
            ));
        }
    }

    /**
     * Info Log
     *
     * Allowed level: 1,2
     * Debug: off/on
     *
     * @param string $message
     * @return void
     */
    public static function info(string $message): void
    {
        self::writeIsNotLevel(sprintf("[%s] %s | %s",
            date(self::$dateFormat),
            '[INFO]',
            $message . PHP_EOL
        ), 0);
    }

    /**
     * Notice Log
     *
     * Allowed level: 1,2
     * Debug: off/on
     *
     * @param string $message
     * @return void
     */
    public static function notice(string $message): void
    {
        self::writeIsNotLevel(sprintf("[%s] %s | %s",
            date(self::$dateFormat),
            '[NOTICE]',
            $message . PHP_EOL
        ), 0);
    }

    /**
     * Warning Log
     *
     * Allowed level: 1,2
     * Debug: off/on
     *
     * @param string $message
     * @return void
     */
    public static function warning(string $message): void
    {
        self::writeIsNotLevel(sprintf("[%s] %s | %s",
            date(self::$dateFormat),
            '[WARNING]',
            $message . PHP_EOL
        ), 0);
    }

    /**
     * Error Log
     *
     * Allowed level: 1,2
     * Debug: off/on
     *
     * @param string $message
     * @return void
     */
    public static function error(string $message): void
    {
        self::writeIsNotLevel(sprintf("[%s] %s | %s",
            date(self::$dateFormat),
            '[ERROR]',
            $message . PHP_EOL
        ), 0);
    }

    /**
     * Critical Log
     *
     * Allowed level: 0,1,2
     * Debug: off/on
     *
     * @param string $message
     * @return void
     */
    public static function critical(string $message): void
    {
        self::write(sprintf("[%s] %s | %s",
            date(self::$dateFormat),
            '[CRITICAL]',
            $message . PHP_EOL
        ));
    }

    /**
     * Alert Log
     *
     * Allowed level: 1,2
     * Debug: off/on
     *
     * @param string $message
     * @return void
     */
    public static function alert(string $message): void
    {
        self::writeIsNotLevel(sprintf("[%s] %s | %s",
            date(self::$dateFormat),
            '[ALERT]',
            $message . PHP_EOL
        ), 0);
    }

    /**
     * Emergency Log
     *
     * Allowed level: 0,1,2
     * Debug: off/on
     *
     * @param string $message
     * @return void
     */
    public static function emergency(string $message): void
    {
        self::write(sprintf("[%s] %s | %s",
            date(self::$dateFormat),
            '[EMERGENCY]',
            $message . PHP_EOL
        ));
    }

}