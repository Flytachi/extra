<?php

namespace App\Utils;

use Flytachi\Extra\Src\Factory\Http\HttpCode;
use Flytachi\Extra\Src\Factory\Http\Response\Response;

class Resp extends Response
{
    public function __construct(mixed $content, mixed $httpCode = HttpCode::OK)
    {
        $content = [
            'statusCode' => $httpCode->value,
            'statusDescription' => $httpCode->message(),
            'data' => json_decode(json_encode($content), true),
        ];
        parent::__construct($content, $httpCode);
    }
}
