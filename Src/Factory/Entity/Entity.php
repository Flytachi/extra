<?php

namespace Extra\Src\Factory\Entity;

use Extra\Src\HttpCode;

abstract class Entity
{
    protected HttpCode $catchHttpCode = HttpCode::INTERNAL_SERVER_ERROR;

    protected function __construct(array $data)
    {
        try {
            $reflection = new \ReflectionClass($this);
            $properties = [];

            foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                $types = $property->getType() instanceof \ReflectionNamedType
                    ? [$property->getType()->getName()]
                    : array_map(fn($type) => $type->getName(), $property->getType()->getTypes());
                $types = array_merge($types, $property->getType()->allowsNull() ? ['NULL'] : []);
                $properties[$property->getName()] = [
                    'types' => $types, // Допустимые типы
                    'required' => !$property->hasDefaultValue(), // Обязательное ли
                ];
            }

            if ($data) {
                foreach ($data as $name => $value) {
                    $validName = array_key_exists($name, $properties);
                    if (!$validName)
                        EntityError::throw($this->catchHttpCode, (env('DEBUG') ? static::class . ': ': '') . "Undefined field '{$name}'");

                    $valueType = gettype($value);
                    $valueType = in_array($valueType, ['integer', 'double', 'boolean'])
                        ? ($valueType === 'integer' ? 'int' : ($valueType === 'double' ? 'float' : 'bool'))
                        : $valueType;

                    $expectedTypes = $properties[$name]['types'];
                    if (!in_array($valueType, $expectedTypes))
                        EntityError::throw($this->catchHttpCode,(env('DEBUG') ? static::class . ': ': '') . "Invalid type field '{$name}'");

                    $this->{$name} = $value;
                    unset($properties[$name]);
                }
            }

            $missingRequired = array_filter($properties, fn ($config) => $config['required']);
            if (!empty($missingRequired)) {
                $missingFields = implode(', ', array_keys($missingRequired));
                EntityError::throw($this->catchHttpCode,(env('DEBUG') ? static::class . ': ': '') . "Required fields not found ({$missingFields})");
            }

            unset($properties);
        } catch (\ReflectionException $exception) {
            EntityError::throw($this->catchHttpCode, (env('DEBUG') ? static::class . ': ': '') . $exception->getMessage());
        }
    }
}