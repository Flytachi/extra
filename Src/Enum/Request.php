<?php

namespace Extra\Src\Enum;

use Extra\Src\Error\ExtraException;
use Extra\Src\Error\RequestError;
use Extra\Src\Route\Route;

class Request
{
    /**
     * @var array<string, string> $headers
     */
    private static array $headers = [];

    public static function setHeaders(): void
    {
        if (function_exists('apache_request_headers')) {
            self::$headers = apache_request_headers();
            self::$headers = array_combine(array_map('ucwords', array_keys(apache_request_headers())), array_values(apache_request_headers()));
        }
    }

    /**
     * @param string $key
     * @param bool $isUcWords
     * @return string
     */
    public static function getHeader(string $key, bool $isUcWords = true): string
    {
        return self::$headers[($isUcWords ? ucwords($key) : $key)] ?? '';
    }

    /**
     * @param bool $required
     * @return array
     */
    public static function get(bool $required = true): array
    {
        if ($required && !$_GET) RequestError::throw(HttpCode::BAD_REQUEST, "There is no GET data in the request.");
        return $_GET;
    }

    /**
     * @param bool $required
     * @return array
     */
    public static function post(bool $required = true): array
    {
        if ($required && !$_POST) RequestError::throw(HttpCode::BAD_REQUEST, "There is no POST data in the request.");
        return $_POST;
    }

    /**
     * @param bool $required
     * @return array
     */
    public static function form(bool $required = true): array
    {
        if ($required && !$_POST) RequestError::throw(HttpCode::BAD_REQUEST, "There is no POST data in the request.");
        return $_POST;
    }

    /**
     * @param bool $required
     * @return array
     */
    public static function json(bool $required = true): array
    {
        $data = file_get_contents('php://input');
        if ($required && !$data) RequestError::throw(HttpCode::BAD_REQUEST, "There is no JSON data in the request.");
        return json_decode($data, true);
    }

    /**
     * @return array
     */
    public static function files(): array
    {
        if (!$_FILES) RequestError::throw(HttpCode::BAD_REQUEST, "There is no FILE data in the request.");
        $data = [];
        foreach ($_FILES as $fileName => $fileData) {
            $data[$fileName] = [];
            foreach ($fileData as $fileDataKey => $fileDataItem) {
                if (is_array($fileDataItem)) {
                    foreach ($fileDataItem as $iKey => $iValue)
                        $data[$fileName][$iKey][$fileDataKey] = $iValue;
                } else $data[$fileName][$fileDataKey] = $fileDataItem;
            }
        }
        return $data;
    }
}