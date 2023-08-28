<?php

namespace Extra\Src\Log;

interface LoggerInterface {
    /**
     * @param string $message message logging
     * @return void
     */
    public static function info(string $message): void;
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
}