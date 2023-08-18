<?php

namespace Extra\Src\Annotation\Postman\Auth;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class PostmanAuthBasicAuth implements PostmanAuthInterface
{
    private string $username;
    private string $password;

    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function prepare(array &$arrayData): void
    {
        $arrayData['request']['auth'] = $this->meta();
    }

    public function meta(): array
    {
        return [
            'type' => 'basic',
            'basic' => [
                [
                    'key' => 'password',
                    'value' => $this->password,
                    'type' => 'string'
                ],
                [
                    'key' => 'username',
                    'value' => $this->username,
                    'type' => 'string'
                ]
            ]
        ];
    }

}