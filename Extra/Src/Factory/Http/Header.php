<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Http;

abstract class Header
{
    /**
     * @var array<string, string> $headers
     */
    private static array $headers = [];

    /**
     * Sets the headers for the request.
     *
     * This method retrieves the request headers and sets them in the $headers property of the class.
     * If the apache_request_headers() function is available, it is used to retrieve the headers.
     * The headers are then formatted using ucwords() and array_combine() functions to ensure consistent formatting.
     *
     * @return void
     */
    public static function setHeaders(): void
    {
        if (function_exists('apache_request_headers')) {
            static::$headers = apache_request_headers();
            static::$headers = array_combine(
                array_map('ucwords', array_keys(apache_request_headers())),
                array_values(apache_request_headers())
            );
        }
        if (isset($_SERVER['REMOTE_ADDR'])) {
            self::$headers['Ip-Address'] = $_SERVER['REMOTE_ADDR'];
        }
//        if (file_exists(PATH_APP . '/Config/security.php')) include PATH_APP . '/Config/security.php';
//        if ($_SERVER['REQUEST_METHOD'] == Method::OPTIONS->name) Response::text(HttpCode::NO_CONTENT);
    }

    /**
     * Retrieves the values in header from the request.
     * @return array<string, string>
     */
    public static function getHeaders(): array
    {
        return static::$headers;
    }


    /**
     * @return string
     */
    public static function getIpAddress(): string
    {
        return static::$headers['Ip-Address'];
    }

    /**
     * Retrieves the value of a specific header from the request.
     *
     * @param string $key The key of the header to retrieve.
     * @param bool $isUcWords (Optional) Specifies whether the key should
     * be formatted with ucwords before retrieving the value. Default is true.
     *
     * @return string The value of the requested header. If the header is not found, an empty string is returned.
     */
    public static function getHeader(string $key, bool $isUcWords = true): string
    {
        return static::$headers[($isUcWords ? ucwords($key) : $key)] ?? '';
    }

    /**
     * Checks if a given key-value pair exists in the headers.
     *
     * @param string $key The key of the header to check.
     * @param string $value The value of the header to check.
     * @param bool $isUcWords (Optional) Specifies whether the key should
     * be converted to ucwords format before checking. Default is true.
     *
     * @return bool Returns true if the key-value pair exists in the headers, false otherwise.
     */
    public static function inHeader(string $key, string $value, bool $isUcWords = true): bool
    {
        return str_contains((static::$headers[($isUcWords ? ucwords($key) : $key)] ?? ''), $value);
    }

    /**
     * Bearer Token
     *
     * @return string|null
     */
    final public static function getBearerToken(): string|null
    {
        if ($auth = static::$headers['Authorization'] ?? '') {
            if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
                return $matches[1];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Basic Token
     *
     * @return string|null
     */
    final public static function getBasicToken(): string|null
    {
        if ($auth = static::$headers['Authorization'] ?? '') {
            if (preg_match('/Basic\s(\S+)/', $auth, $matches)) {
                return base64_decode($matches[1]);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
