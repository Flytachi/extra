<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Log;

use DateTimeZone;
use Flytachi\Extra\Extra;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;

class ExtraLogger extends \Monolog\Logger
{
    public function __construct(
        string $name,
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    ) {
        parent::__construct($name, $handlers, $processors, $timezone);

        $loggerStreamHandler = new RotatingFileHandler(
            Extra::$pathStorageLog . '/frame.log',
            maxFiles: env('LOGGER_MAX_FILES', 0),
            dateFormat: env('LOGGER_FILE_DATE_FORMAT', 'Y-m-d')
        );
        $loggerStreamHandler->setFormatter(new LineFormatter(
            dateFormat: env('LOGGER_LINE_DATE_FORMAT', 'Y-m-d H:i:s P'),
            allowInlineLineBreaks: true,
            ignoreEmptyContextAndExtra: true
        ));

        $allowedLevels = env('LOGGER_LEVEL_ALLOW');
        if ($allowedLevels === null) {
            $allowedLevels = 'DEBUG,INFO,NOTICE,WARNING,ERROR,CRITICAL,ALERT,EMERGENCY';
        }
        $allowedLevels = array_map('trim', explode(',', $allowedLevels));
        $levelMap = [
            'DEBUG' => \Monolog\Logger::DEBUG,
            'INFO' => \Monolog\Logger::INFO,
            'NOTICE' => \Monolog\Logger::NOTICE,
            'WARNING' => \Monolog\Logger::WARNING,
            'ERROR' => \Monolog\Logger::ERROR,
            'CRITICAL' => \Monolog\Logger::CRITICAL,
            'ALERT' => \Monolog\Logger::ALERT,
            'EMERGENCY' => \Monolog\Logger::EMERGENCY,
        ];

        $allowedLevels = array_map(fn($level) => $levelMap[strtoupper($level)] ?? null, $allowedLevels);
        $allowedLevels = array_filter($allowedLevels);

        $filterHandler = new FilterHandler($loggerStreamHandler, $allowedLevels, \Monolog\Logger::DEBUG);

        $this->pushHandler($filterHandler);
    }
}
