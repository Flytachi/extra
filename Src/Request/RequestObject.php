<?php

namespace Extra\Src\Request;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Log\Log;
use ReflectionProperty;

abstract class RequestObject
{
    protected function validation(): void
    {}

    public final static function request(string $dataType = 'get'): static
    {
        if (!in_array($dataType, ['get', 'post', 'json', 'form', 'files']))
            RequestError::throw(HttpCode::INTERNAL_SERVER_ERROR, "Unsupported request data type: {$dataType}");
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
        Log::trace('Request valid: ' . $field);
        try {
            if(!property_exists($this, $field))
                RequestError::throw(HttpCode::BAD_REQUEST,"Field \"{$field}\" not found!");
            if ($validateFunc !== null) {
                if (!$validateFunc($this->{$field}))
                    RequestError::throw(HttpCode::BAD_REQUEST, "{$field} - " . ($message ?? "field has the wrong data type!"));
            }
        } catch (\Throwable $exception) {
            RequestError::throw(HttpCode::BAD_REQUEST, $exception->getMessage());
        }
        return $this;
    }

    public final function __construct(array $data)
    {
        try {
            $reflection = new \ReflectionClass($this);
            $property1 = [];
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty)
                $property1[$reflectionProperty->getName()] = (string)$reflectionProperty->getType();
            $properties = $property1;
            if ($data) {
                foreach ($data as $key => $value) {
                    if (!array_key_exists($key, $properties))
                        RequestError::throw(HttpCode::BAD_REQUEST,"Undefined Field \"{$key}\"");
                    $this->{$key} = $value;
                    unset($properties[$key]);
                }
                foreach ($properties as $property => $type) {
                    if (gettype($this->{$property})) unset($properties[$property]);
                }
            }
        } catch (\Throwable $exception) {
            RequestError::throw(HttpCode::BAD_REQUEST, $exception->getMessage());
        }
        $this->validation();
    }

}