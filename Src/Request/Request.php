<?php

namespace Extra\Src\Request;

use Extra\Src\HttpCode;
use Extra\Src\Log\Log;

/**
 * Class Request
 *
 * The `Request` class is useful for processing HTTP requests. It provides a set of static
 * methods to handle different types of requests (GET, POST, JSON, Form-data and Files).
 *
 * The methods provided by `Request` class include:
 *
 * - `setHeaders(): void`: This method is used to set the request headers.
 * - `getHeader(string $key, bool $isUcWords = true): string`: This method is used to obtain a specific request header.
 * - `inHeader(string $key, string $value, bool $isUcWords = true): bool`: This method checks if a specific value is contained in a specific request header.
 * - `get(bool $required = true): self`: This method retrieves the data from GET request.
 * - `post(bool $required = true): self`: This method retrieves the data from POST request.
 * - `form(bool $required = true): self`: This method retrieves the data from FORM request.
 * - `json(bool $required = true): self`: This method retrieves the data from JSON request.
 * - `files(): self`: This method retrieves the data from Files request.
 * - `valid(string $field, callable $validateFunc = null, string $message = null): self`: This method validates a specific field in the request data.
 *
 * @version 1.0
 * @author Flytachi
 */
class Request
{
    /**
     * @var array<string, string> $headers
     */
    private static array $headers = [];
    private array $data = [];

    /**
     * @param array $data
     */
    public final function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Retrieves the data stored in the object.
     *
     * @return array The data stored in the object as an associative array.
     */
    public final function getData(): array
    {
        return $this->data;
    }

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
            self::$headers = apache_request_headers();
            self::$headers = array_combine(array_map('ucwords', array_keys(apache_request_headers())), array_values(apache_request_headers()));
        }
    }

    /**
     * Retrieves the value of a specific header from the request.
     *
     * @param string $key The key of the header to retrieve.
     * @param bool $isUcWords (Optional) Specifies whether the key should be formatted with ucwords before retrieving the value. Default is true.
     *
     * @return string The value of the requested header. If the header is not found, an empty string is returned.
     */
    public static function getHeader(string $key, bool $isUcWords = true): string
    {
        return self::$headers[($isUcWords ? ucwords($key) : $key)] ?? '';
    }

    /**
     * Checks if a given key-value pair exists in the headers.
     *
     * @param string $key The key of the header to check.
     * @param string $value The value of the header to check.
     * @param bool $isUcWords (Optional) Specifies whether the key should be converted to ucwords format before checking. Default is true.
     *
     * @return bool Returns true if the key-value pair exists in the headers, false otherwise.
     */
    public static function inHeader(string $key, string $value, bool $isUcWords = true): bool
    {
        return str_contains((self::$headers[($isUcWords ? ucwords($key) : $key)] ?? ''), $value);
    }

    /**
     * Retrieves the GET data from the request.
     *
     * @param bool $required (Optional) Specifies whether the GET data is required. Default is true.
     *
     * @return self A new instance of the class representing the GET data from the request.
     */
    public static function get(bool $required = true): self
    {
        if ($required && !$_GET) RequestError::throw(HttpCode::BAD_REQUEST, "There is no GET data in the request.");
        return new self($_GET);
    }

    /**
     * Retrieves the POST data from the request.
     *
     * @param bool $required (Optional) Specifies whether the POST data is required. Default is true.
     *
     * @return self A new instance of the class representing the POST data from the request.
     */
    public static function post(bool $required = true): self
    {
        if ($required && !$_POST) RequestError::throw(HttpCode::BAD_REQUEST, "There is no POST data in the request.");
        return new self($_POST);
    }

    /**
     * Retrieves the POST data from the request.
     *
     * @param bool $required (Optional) Specifies whether the POST data is required. Default is true.
     *
     * @return self A new instance of the class representing the POST data from the request.
     */
    public static function form(bool $required = true): self
    {
        if ($required && !$_POST) RequestError::throw(HttpCode::BAD_REQUEST, "There is no POST data in the request.");
        return new self($_POST);
    }

    /**
     * Retrieves the JSON data from the request.
     *
     * @param bool $required (Optional) Specifies whether the JSON data is required. Default is true.
     *
     * @return self A new instance of the class representing the JSON data from the request.
     */
    public static function json(bool $required = true): self
    {
        $data = file_get_contents('php://input');
        if ($required && !$data) RequestError::throw(HttpCode::BAD_REQUEST, "There is no JSON data in the request.");
        return new self(json_decode($data, true) ?? []);
    }

    /**
     * Retrieves the FILE data from the request.
     *
     * @return self A new instance of the class representing the FILE data from the request.
     */
    public static function files(): self
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
        return new self($data);
    }

    /**
     * Validate Field
     *
     * Checking the existence of a value in the data.
     *
     * If you set the argument "validateFunc" will check the
     * data on the function with the condition that the
     * function returns a bool value, and takes 1 argument
     *
     * @param string $field field name -> array key
     * @param callable|null $validateFunc validation func returned bool!
     * @param string|null $message message with incorrect validation in func
     *
     * @return self
     */
    public final function valid(string $field, callable $validateFunc = null, string $message = null): self
    {
        Log::trace(self::class . ' valid: ' . $field);
        try {
            if(!array_key_exists($field, $this->data))
                RequestError::throw(HttpCode::BAD_REQUEST,"Field \"{$field}\" not found!");
            if ($validateFunc !== null) {
                if (!$validateFunc($this->data[$field]))
                    RequestError::throw(HttpCode::BAD_REQUEST, "{$field} - " . ($message ?? "field has the wrong data type!"));
            }
        } catch (\Throwable $exception) {
            RequestError::throw(HttpCode::BAD_REQUEST, $exception->getMessage());
        }
        return $this;
    }
}