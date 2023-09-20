<?php

namespace Extra\Src\Log;

interface LoggerInterface {
    /**
     * @param string $message message logging
     * @return void
     */
    public static function trace(string $message): void;
    /**
     * @param string $message message logging
     * @return void
     */
    public static function debug(string $message): void;
    /**
     * @param string $message message logging
     * @return void
     */
    public static function info(string $message): void;
    /**
     * @param string $message message logging
     * @return void
     */
    public static function notice(string $message): void;
    /**
     * @param string $message message logging
     * @return void
     */
    public static function warning(string $message): void;
    /**
     * @param string $message message logging
     * @return void
     */
    public static function error(string $message): void;
    /**
     * @param string $message message logging
     * @return void
     */
    public static function critical(string $message): void;
    /**
     * @param string $message message logging
     * @return void
     */
    public static function alert(string $message): void;
    /**
     * @param string $message message logging
     * @return void
     */
    public static function emergency(string $message): void;
}