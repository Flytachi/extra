<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\OpenApi\Schema;

use Attribute;
use Flytachi\Extra\Src\Factory\Http\HttpCode;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class SplResponse implements Spl
{
    private HttpCode $httpCode;
    private mixed $data = null;
    private ?string $description = null;

    /**
     * @param HttpCode $httpCode
     * @param mixed|null $data
     * @param string|null $description
     */
    public function __construct(HttpCode $httpCode, mixed $data = null, ?string $description = null)
    {
        $this->httpCode = $httpCode;
        $this->data = $data;
        $this->description = $description;
    }


    public function modify(array &$path): void
    {
        if (!isset($path['responses'])) {
            $path['responses'] = [];
        }
        if ($this->httpCode->isSuccess()) {
            $data = [
                'data' => [
                    'type' => gettype($this->data),
                    'example' => $this->data
                ]
            ];
        } else {
            $data = [
                'message' => [
                    'type' => gettype($this->data),
                    'example' => $this->data
                ]
            ];
        }

        $path['responses'][$this->httpCode->value] = [
            'description' => $this->description,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => [
                                'type' => 'integer',
                                'format' => 'int',
                                'example' => $this->httpCode->value
                            ],
                            'statusDescription' => [
                                'type' => 'string',
                                'example' => $this->httpCode->name
                            ],
                            ...$data
                        ]
                    ]
                ]
            ]
        ];
    }
}
