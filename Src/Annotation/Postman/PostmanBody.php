<?php

namespace Extra\Src\Annotation\Postman;

use Attribute;
use Extra\Src\Enum\Postman\PostmanDataType;

#[Attribute(Attribute::TARGET_METHOD)]
class PostmanBody implements Postman
{
    private PostmanDataType $dataType;
    private array|string $data;

    /**
     * @param PostmanDataType $dataType
     * @param array|string $data
     */
    public function __construct(PostmanDataType $dataType, array|string $data)
    {
        $this->dataType = $dataType;
        $this->data = $data;
    }

    public function prepare(array &$arrayData): void
    {
        switch ($this->dataType) {
            case PostmanDataType::FORM:
                $arrayData['request']['body'] = [
                    'mode' => 'formdata',
                    'formdata' => []
                ];
                foreach ($this->data as $itemName => $itemValue) {
                    $arrayData['request']['body']['formdata'][] = [
                        'key' => $itemName,
                        'value' => $itemValue,
                        'type' => 'text'
                    ];
                }
                break;
            case PostmanDataType::JSON:
                $arrayData['request']['header'][] = [
                    'key' => 'Content-Type',
                    'value' => 'application/json',
                    'type' => 'text',
                ];
                $arrayData['request']['body'] = [
                    'mode' => 'raw',
                    'raw' => json_encode($this->data, JSON_PRETTY_PRINT),
                    'options' => [
                        'raw' => ['language' => 'json']
                    ]
                ];
                break;
            case PostmanDataType::TEXT:
                $arrayData['request']['body'] = [
                    'mode' => 'raw',
                    'raw' => $this->data,
                ];
                break;
        }
    }

}