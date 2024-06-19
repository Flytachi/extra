<?php

namespace Extra\Src\Log;

/**
 * Class LoggerBase
 *
 * `LoggerBase` is an abstract base class for defining specific logger mechanisms. It offers core functionalities common to all loggers.
 *
 * The methods provided by `LoggerBase` include:
 *
 * - `initial(): void`: Initialises the logger, handling file creation and logging resource setup.
 * - `write(string $message): void`: Writes a log message to the log file.
 * - `writeIsLevel(string $message, int $level): void`: Writes a log message if the current level equals the provided level.
 * - `writeIsNotLevel(string $message, int $level): void`: Writes a log message if the current level does not match the provided level.
 * - `writeIsDebug(string $message): void`: Writes a log message if debug is on.
 * - `setType(LoggerType $type = LoggerType::STACK): void`: Sets the logger's type which can be STACK, DAILY, or MONTHLY.
 * - `setFormat(string $format = 'Y-m-d H:i:s P'): void`: Sets the date format used in log messages.
 * - `setLevel(int $level = 0): void`: Sets the log level (0 - no logging, 1 - default logging, 2 - trace logging).
 * - `setLifeTime(int $lifeTime = 0): void`: Sets the log lifetime.
 * - `prepareCallable(callable|null $prepareFunction = null): void`: Sets a callable to be used in preparing log messages.
 *
 * @version 1.2
 * @author Flytachi
 */
abstract class LoggerBase {
    /**
     * @var string $handle file name
     */
    protected static string $handle = 'base';
    /**
     * @var false|resource $resource
     */
    protected static mixed $resource = null;
    protected static LoggerType $type = LoggerType::STACK;
    protected static string $dateFormat = 'Y-m-d H:i:s P';
    private static int $level = 0;
    private static int $lifeTime = 0;
    private static string|null $prepareFunction = null;

    private static function initial(): void
    {
        if (is_null(static::$resource)) {
            if (is_writable(PATH_LOG)) {
                $file = match (static::$type) {
                    LoggerType::STACK => PATH_LOG . '/' . static::$handle . '.log',
                    LoggerType::DAILY => PATH_LOG . '/' . static::$handle . '-' . date("Y-m-d") . '.log',
                    LoggerType::MONTHLY => PATH_LOG . '/' . static::$handle . '-' . date("Y-m") . '.log',
                };

                if (!file_exists($file)) {
                    if (self::$lifeTime != 0) {
                        $list = glob(PATH_LOG . '/*');
                        $over = count($list)+1 - self::$lifeTime;
                        for ($i = 0; $i < $over; $i++) unlink($list[$i]);
                    }
                    file_put_contents($file,'');
                    chmod($file,0777);
                }
                static::$resource = fopen($file, 'a');
            } else static::$resource = false;
        }
    }

    protected final static function write(string $message): void
    {
        static::initial();
        if (static::$resource !== false) fwrite(static::$resource, $message);
        if (static::$prepareFunction != null) (static::$prepareFunction)($message);
    }

    protected final static function writeIsLevel(string $message, int $level): void
    {
        if (static::$level === $level) {
            static::initial();
            if (static::$resource !== false) fwrite(static::$resource, $message);
            if (static::$prepareFunction != null) (static::$prepareFunction)($message);
        }
    }

    protected final static function writeIsNotLevel(string $message, int $level): void
    {
        if (static::$level !== $level) {
            static::initial();
            if (static::$resource !== false) fwrite(static::$resource, $message);
            if (static::$prepareFunction != null) (static::$prepareFunction)($message);
        }
    }

    protected final static function writeIsDebug(string $message): void
    {
        if (env('DEBUG')) {
            static::initial();
            if (static::$resource !== false) fwrite(static::$resource, $message);
            if (static::$prepareFunction != null) (static::$prepareFunction)($message);
        }
    }

    /**
     * @param LoggerType $type
     * @return void
     */
    public final static function setType(LoggerType $type = LoggerType::STACK): void
    {
        static::$type = $type;
    }

    /**
     * @param string $format
     * @return void
     */
    public final static function setFormat(string $format = 'Y-m-d H:i:s P'): void
    {
        static::$dateFormat = $format;
    }

    /**
     * @param int $level (0 - no logging, 1 - default logging, 2 - trace logging)
     * @return void
     */
    public final static function setLevel(int $level = 0): void
    {
        static::$level = $level;
    }

    /**
     * @param int $lifeTime count log file (0 - dont limit)
     * @return void
     */
    public static function setLifeTime(int $lifeTime = 0): void
    {
        self::$lifeTime = $lifeTime;
    }

    public static function prepareCallable(callable|null $prepareFunction = null): void
    {
        self::$prepareFunction = $prepareFunction;
    }
}