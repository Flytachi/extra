<?php

namespace Extra\Src\Factory\Mapping\OpenApi\Schema;

use Attribute;
use Extra\Src\Factory\Mapping\OpenApi\Common\DataType;
use Extra\Src\HttpCode;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class SplRequest implements Spl
{
    private DataType $dataType;
    private array|string|null $data = null;

    /**
     * @param DataType $dataType
     * @param array|string|null $data
     */
    public function __construct(DataType $dataType, array|string|null $data = null)
    {
        $this->dataType = $dataType;
        $this->data = $data;
    }


    public function modify(array &$path): void
    {
        switch ($this->dataType) {
            case DataType::JSON:
                $type = 'application/json';
                if (is_array($this->data)) $this->setData($path, $type);
                elseif (is_string($this->data)) $this->setDataObject($path, $type);
                break;
            case DataType::FORM:
                $type = 'multipart/form-data';
                if (is_array($this->data)) $this->setData($path, $type);
                elseif (is_string($this->data)) $this->setDataObject($path, $type);
                break;
            case DataType::QUERY:
                if (is_array($this->data)) $this->setQueryData($path);
                elseif (is_string($this->data)) $this->setQueryDataObject($path);
                break;
        }
    }

    private function setData(array &$path, string $type): void
    {
        $properties = [];
        foreach ($this->data as $key => $value) {
            $properties[$key] = [
                // 'type' => gettype($value),
                'default' => $value
            ];
        }

        if (!empty($properties)) {
            if (!isset($path['requestBody'])) $path['requestBody'] = [];
            if (!isset($path['requestBody']['content'])) $path['requestBody']['content'] = [];
            $path['requestBody']['content'][$type]['schema'] = [
                'type' => 'object', 'properties' => $properties
            ];
        }
    }

    private function setDataObject(array &$path, string $type): void
    {
        $reflection = new \ReflectionClass($this->data);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPublic()) {
                $properties[$property->getName()] = [
//                    'type' => $property->getType()->getName(),
                ];
                if ($property->hasDefaultValue())
                    $properties[$property->getName()]['default'] = $property->getDefaultValue();
            }
        }

        if (!empty($properties)) {
            if (!isset($path['requestBody'])) $path['requestBody'] = [];
            if (!isset($path['requestBody']['content'])) $path['requestBody']['content'] = [];
            $path['requestBody']['content'][$type]['schema'] = [
                'type' => 'object', 'properties' => $properties
            ];
        }
    }

    private function setQueryData(array &$path): void
    {
        if (!isset($path['parameters'])) $path['parameters'] = [];
        $path['parameters'] = [];

        foreach ($this->data as $key => $value) {
            $object = [
                'name' => $key,
                'in' => 'query',
                'schema' => [
                    // 'type' => gettype($value),
                    'default' => $value
                ]
            ];
            $path['parameters'][] = $object;
        }
    }

    private function setQueryDataObject(array &$path): void
    {
        if (!isset($path['parameters'])) $path['parameters'] = [];
        $path['parameters'] = [];
        $reflection = new \ReflectionClass($this->data);

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPublic()) {
                $properties[$property->getName()] = [
//                    'type' => $property->getType()->getName(),
                ];
                if ($property->hasDefaultValue())
                    $properties[$property->getName()]['example'] = $property->getDefaultValue();

                $object = [
                    'name' => $property->getName(),
                    'in' => 'query',
                    'schema' => [
                        // 'type' => gettype($value),
                        'default' => $property->hasDefaultValue() ? $property->getDefaultValue() : null
                    ]
                ];
                $path['parameters'][] = $object;
            }
        }
    }

}