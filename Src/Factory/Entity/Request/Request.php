<?php

namespace Extra\Src\Factory\Entity\Request;

use Extra\Src\Controller\Method;
use Extra\Src\Factory\Entity\EntityError;
use Extra\Src\Factory\Entity\Request\Common\RequestHeaderTrait;
use Extra\Src\Factory\Entity\Request\Common\RequestObjectTrait;
use Extra\Src\Factory\Response\Response;
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
 * @version 1.2
 * @author Flytachi
 */
class Request
{
    use RequestHeaderTrait, RequestObjectTrait;
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
                EntityError::throw(HttpCode::BAD_REQUEST,"Field \"{$field}\" not found!");
            if ($validateFunc !== null) {
                if (!$validateFunc($this->data[$field]))
                    EntityError::throw(HttpCode::BAD_REQUEST, "{$field} - " . ($message ?? "field has the wrong data type!"));
            }
        } catch (\Throwable $exception) {
            EntityError::throw(HttpCode::BAD_REQUEST, $exception->getMessage());
        }
        return $this;
    }
}