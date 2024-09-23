<?php

namespace Extra\Src\Factory\Mapping\OpenApi\Schema;

use Attribute;
use Extra\Src\Factory\Mapping\OpenApi\Common\DataType;
use Extra\Src\HttpCode;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class SplRequest implements Spl
{
    private DataType $dataType;
    private mixed $data = null;

    /**
     * @param DataType $dataType
     * @param mixed|null $data
     */
    public function __construct(DataType $dataType, mixed $data = null)
    {
        $this->dataType = $dataType;
        $this->data = $data;
    }


    public function modify(array &$path): void
    {
        switch ($this->dataType) {
            case DataType::JSON:
                $type = 'application/json';
                $properties = [];
                foreach ($this->data as $key => $value) {
                    $properties[$key] = [
                        // 'type' => gettype($value),
                        'example' => $value
                    ];
                }

                if (!empty($properties)) {
                    if (!isset($path['requestBody'])) $path['requestBody'] = [];
                    if (!isset($path['requestBody']['content'])) $path['requestBody']['content'] = [];
                    $path['requestBody']['content'][$type]['schema'] = [
                        'type' => 'object', 'properties' => $properties
                    ];
                }
                break;
            case DataType::FORM:
                $type = 'application/x-www-form-urlencoded';
                $properties = [];
                foreach ($this->data as $key => $value) {
                    $properties[$key] = [
                        // 'type' => gettype($value),
                        'example' => $value
                    ];
                }

                if (!empty($properties)) {
                    if (!isset($path['requestBody'])) $path['requestBody'] = [];
                    if (!isset($path['requestBody']['content'])) $path['requestBody']['content'] = [];
                    $path['requestBody']['content'][$type]['schema'] = [
                        'type' => 'object', 'properties' => $properties
                    ];
                }
                break;
            case DataType::QUERY:
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
                break;
        }

    }
}