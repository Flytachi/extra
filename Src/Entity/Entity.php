<?php

namespace Extra\Src\Entity;

use Extra\Src\HttpCode;
use ReflectionProperty;

abstract class Entity
{
    protected function __construct(array $data)
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
                        EntityError::throw(HttpCode::BAD_REQUEST,"Undefined Field \"{$key}\"");
                    $this->{$key} = $value;
                    unset($properties[$key]);
                }
                foreach ($properties as $property => $type) {
                    if (gettype($this->{$property})) unset($properties[$property]);
                }
            }
        } catch (\Throwable $exception) {
            EntityError::throw(HttpCode::BAD_REQUEST, $exception->getMessage());
        }
    }
}