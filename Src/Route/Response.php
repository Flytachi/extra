<?php

namespace Extra\Src\Route;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Log\Log;

class Response
{
    /**
     * Api Response
     *
     * @param HttpCode $httpCode
     * @param mixed $data data
     *
     * @return never
     */
    final static function json(HttpCode $httpCode, mixed $data = null): never
    {
        self::setHeaders($httpCode, 'application/json');

        Log::trace($_SERVER['REQUEST_URI'] . '[' . $httpCode->value .  '] => ' . json_encode($data));
        echo json_encode([
            'statusCode' => $httpCode->value,
            'statusDescription' => $httpCode->message(),
            'data' => $data,
            ...self::debugApi()
        ]);
        die;
    }

    /**
     * Api Response Message
     *
     * @param HttpCode $httpCode
     * @param string $message
     *
     * @return never
     */
    final static function jsonMessage(HttpCode $httpCode, string $message = ''): never
    {
        self::setHeaders($httpCode, 'application/json');

        Log::trace($_SERVER['REQUEST_URI'] . '[' . $httpCode->value . '] => ' . $message);
        echo json_encode([
            'statusCode' => $httpCode->value,
            'statusDescription' => $httpCode->message(),
            'message' => $message,
            ...self::debugApi()
        ]);
        die;
    }

    /**
     * Text Response
     *
     * @param HttpCode $httpCode
     * @param string $text
     * @param bool $htmlEntities
     *
     * @return never
     */
    final static function text(HttpCode $httpCode, string $text = '', bool $htmlEntities = false): never
    {
        self::setHeaders($httpCode, 'text/plain');

        Log::trace($_SERVER['REQUEST_URI'] . '[' . $httpCode->value . '] => ' . $text);
        echo ($htmlEntities) ? htmlentities($text) : $text;
        die;
    }

    private static function setHeaders(HttpCode $httpCode, string $contentType): void
    {
        header_remove("X-Powered-By");
        header("HTTP/1.1 {$httpCode->value} " . $httpCode->message());
        header("Status: {$httpCode->value} " . $httpCode->message());
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: {$contentType}");
    }

    private static function debugApi(): array
    {
        if (env('DEBUG', false)) {
            $delta = round(microtime(true) - WARFRAME_STARTUP_TIME, 3);
            return [
                'debug' => [
                    'time' => ($delta < 0.001) ? 0.001 : $delta,
                    'date' => date(DATE_ATOM),
                    'timezone' => env('TIME_ZONE', 'UTC'),
                    'sapi' => PHP_SAPI,
                    'memory' => bytes(memory_get_usage(), 'MiB'),
                ]
            ];
        } else return [];
    }
}