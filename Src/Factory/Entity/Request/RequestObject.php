<?php

namespace Extra\Src\Factory\Entity\Request;

use Extra\Src\Factory\Entity\Entity;
use Extra\Src\Factory\Entity\EntityError;
use Extra\Src\HttpCode;
use Extra\Src\Log\Log;


/**
 * Class RequestObject
 *
 * The `RequestObject` is an abstract class providing methods to process and validate HTTP requests data.
 * It provides an interface to easily interact with and validate data from different types of HTTP requests.
 *
 * The methods provided by `RequestObject` include:
 *
 * - `validation(): void`: Abstract method, should be implemented in derived classes and provide custom validation logic for request data.
 * - `get(): static`: Returns an instance of the class with data from a `GET` request.
 * - `post(): static`: Returns an instance of the class with data from a `POST` request.
 * - `json(): static`: Returns an instance of the class with data from a `JSON` request.
 * - `form(): static`: Returns an instance of the class with data from a `FORM` request.
 * - `files(): static`: Returns an instance of the class with data from a `FILES` request.
 * - `request(string $dataType = 'get'): static`: Allows for requesting specific data types ('get', 'post', 'json', 'form', 'files'). It returns a `RequestObject` instance with the requested data.
 * - `valid(string $field, callable $validateFunc = null, string $message = null): static`: Validates a specific field in the request data by checking its existence and optionally verifies it further using a callable `validateFunc` function.
 *
 * @version 1.1
 * @author Flytachi
 */
abstract class RequestObject extends Entity
{
    protected HttpCode $catchHttpCode = HttpCode::BAD_REQUEST;

    protected function validation(): void
    {}

    /**
     * Retrieves data using the "get" method of the Request class and creates a new instance
     * of the current class, using the retrieved data.
     *
     * @return static The new instance of the current class, with the retrieved data.
     */
    public final static function get(): static
    {
        return new static(Request::get(false)->getData());
    }

    /**
     * Retrieves data using the "json" method of the Request class and creates a new instance
     * of the current class, using the retrieved data.
     *
     * @return static The new instance of the current class, with the retrieved data.
     */
    public final static function json(): static
    {
        return new static(Request::json(false)->getData());
    }

    /**
     * Retrieves data using the "post" method of the Request class and creates a new instance
     * of the current class, using the retrieved data.
     *
     * @return static The new instance of the current class, with the retrieved data.
     */
    public final static function post(): static
    {
        return new static(Request::post(false)->getData());
    }

    /**
     * Retrieves data using the "json" method of the Request class and creates a new instance
     * of the current class, using the retrieved data.
     *
     * @return static The new instance of the current class, with the retrieved data.
     */
    public final static function form(): static
    {
        return new static(Request::post(false)->getData());
    }

    /**
     * Retrieves files using the "files" method of the Request class and creates a new instance
     * of the current class, using the retrieved data.
     *
     * @return static The new instance of the current class, with the retrieved files data.
     */
    public final static function files(): static
    {
        return new static(Request::files(false)->getData());
    }

    /**
     * Sends a request to the server and returns the response data.
     *
     * @param string $dataType The type of data to send in the request. Possible values are 'get', 'post', 'json', 'form', 'files'. Defaults to 'get'.
     *
     * @return static The response data.
     */
    public final static function request(string $dataType = 'get'): static
    {
        if (!in_array($dataType, ['get', 'post', 'json', 'form', 'files']))
            EntityError::throw(HttpCode::INTERNAL_SERVER_ERROR, "Unsupported request data type: {$dataType}");
        return new static(Request::{$dataType}(false)->getData());
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
     * @return static
     */
    public final function valid(string $field, callable $validateFunc = null, string $message = null): static
    {
        Log::trace(static::class . ' valid: ' . $field);
        try {
            if(!property_exists($this, $field))
                EntityError::throw(HttpCode::BAD_REQUEST,"Field \"{$field}\" not found!");
            if ($validateFunc !== null) {
                if (!$validateFunc($this->{$field}))
                    EntityError::throw(HttpCode::BAD_REQUEST, "{$field} - " . ($message ?? "field has the wrong data type!"));
            }
        } catch (\Throwable $exception) {
            EntityError::throw(HttpCode::BAD_REQUEST, $exception->getMessage());
        }
        return $this;
    }

    /**
     * Validates a field by a given filter and throws a RequestError if the field is invalid.
     *
     * @param string $field The name of the field to validate.
     * @param int $filter The filter to apply to the field. Defaults to FILTER_DEFAULT.
     * @param string|null $message The custom error message to use if the field is invalid. Defaults to null.
     *
     * @return static The current instance of the class.
     */
    public final function validByFilter(string $field, int $filter = FILTER_DEFAULT, string $message = null): static
    {
        Log::trace(static::class . ' valid: ' . $field);
        try {
            if(!property_exists($this, $field))
                EntityError::throw(HttpCode::BAD_REQUEST,"Field \"{$field}\" not found!");
            if (!filter_var($this->{$field}, $filter))
                EntityError::throw(HttpCode::BAD_REQUEST, "{$field} - " . ($message ?? "field has the wrong data type!"));
        } catch (\Throwable $exception) {
            EntityError::throw(HttpCode::BAD_REQUEST, $exception->getMessage());
        }
        return $this;
    }

    public final function __construct(array $data)
    {
        parent::__construct($data);
        $this->validation();
    }

}