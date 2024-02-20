<?php

namespace Extra\Src\Unit\Postman\Auth;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class PostmanAuthBearerToken implements PostmanAuthInterface
{
    private string $token;

    /**
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function prepare(array &$arrayData): void {
        $arrayData['request']['auth'] = $this->meta();
    }

    public function meta(): array
    {
        return [
            'type' => 'bearer',
            "bearer" => [
                [
                    'key' => 'token',
                    'value' => $this->token,
                    'type' => 'string'
                ]
            ]
        ];
    }
}