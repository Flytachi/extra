<?php

namespace Extra\Src\Error;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\Request;
use Extra\Src\Log\Log;

trait ErrorLogTrait
{
    public static function throw(HttpCode $httpCode, string $message): never
    {
        throw new static($message, $httpCode->value);
    }

    public static function fatal(string $message): never
    {
        throw new static($message, 700);
    }
}