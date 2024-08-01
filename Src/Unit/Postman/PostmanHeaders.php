<?php

namespace Extra\Src\Unit\Postman;

use Attribute;
use Extra\Src\Controller\Method;

#[Attribute(Attribute::TARGET_METHOD)]
class PostmanHeaders implements Postman
{
    /** @var array<string, string> $headers */
    private array $headers;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    public function prepare(array &$arrayData): void
    {
        foreach ($this->headers as $headerKey => $headerValue) {
            $arrayData['request']['header'][] = [
                'key' => $headerKey,
                'value' => $headerValue,
                'type' => 'text',
            ];
        }
    }

}