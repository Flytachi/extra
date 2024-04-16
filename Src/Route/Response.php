<?php

namespace Extra\Src\Route;

use Extra\Src\HttpCode;
use Extra\Src\Log\Log;

/**
 * Class Response
 *
 * `Response` is a class that provides a set of methods to facilitate HTTP responses.
 * It allows you to consistently format and send various HTTP responses such as JSON and plain text.
 *
 * The methods provided by `Response` include:
 *
 * - `json(HttpCode $httpCode, mixed $data = null): never`: Sends a JSON response with a status code and optional data.
 * - `jsonMessage(HttpCode $httpCode, string $message = ''): never`: Sends a JSON response with a status code and a message.
 * - `text(HttpCode $httpCode, string $text = '', bool $htmlEntities = false): never`: Sends a plain text response with a status code. If the `$htmlEntities` flag gets set to true, the `$text` will be escaped using `htmlentities`.
 *
 * @version 1.0
 * @author Flytachi
 */
class Response
{
    /**
     * Api Response JSON
     *
     * @param HttpCode $httpCode The HTTP code for the response
     * @param mixed $data The data to be sent in the response (optional)
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
     * Generates a JSON response message.
     *
     * @param HttpCode $httpCode The HTTP status code object.
     * @param string $message The message to be included in the JSON response. Default is an empty string.
     * @return never This method does not return a value as it terminates the script execution.
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
     * Generates a plain text response message.
     *
     * @param HttpCode $httpCode The HTTP status code object.
     * @param string $text The text message to be included in the response. Default is an empty string.
     * @param bool $htmlEntities Determine whether to encode HTML entities in the text message. Default is false.
     * @return never This method does not return a value as it terminates the script execution.
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
            $delta = round(microtime(true) - EXTRA_STARTUP_TIME, 3);
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