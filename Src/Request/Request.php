<?php

namespace Extra\Src\Request;

use Extra\Src\Enum\HttpCode;

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

    public final function getData(): array
    {
        return $this->data;
    }

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
     * @param string $key
     * @param string $value
     * @param bool $isUcWords
     * @return bool
     */
    public static function inHeader(string $key, string $value, bool $isUcWords = true): bool
    {
        return str_contains((self::$headers[($isUcWords ? ucwords($key) : $key)] ?? ''), $value);
    }

    /**
     * @param bool $required
     * @return self
     */
    public static function get(bool $required = true): self
    {
        if ($required && !$_GET) RequestError::throw(HttpCode::BAD_REQUEST, "There is no GET data in the request.");
        return new self($_GET);
    }

    /**
     * @param bool $required
     * @return self
     */
    public static function post(bool $required = true): self
    {
        if ($required && !$_POST) RequestError::throw(HttpCode::BAD_REQUEST, "There is no POST data in the request.");
        return new self($_POST);
    }

    /**
     * @param bool $required
     * @return self
     */
    public static function form(bool $required = true): self
    {
        if ($required && !$_POST) RequestError::throw(HttpCode::BAD_REQUEST, "There is no POST data in the request.");
        return new self($_POST);
    }

    /**
     * @param bool $required
     * @return self
     */
    public static function json(bool $required = true): self
    {
        $data = file_get_contents('php://input');
        if ($required && !$data) RequestError::throw(HttpCode::BAD_REQUEST, "There is no JSON data in the request.");
        return new self(json_decode($data, true) ?? []);
    }

    /**
     * @return self
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
}