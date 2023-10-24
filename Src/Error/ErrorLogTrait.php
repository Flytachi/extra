<?php

namespace Extra\Src\Error;

use Extra\Src\Enum\HttpCode;

trait ErrorLogTrait
{
    public static function throw(HttpCode $httpCode, string $message, \Throwable|null $previous = null): never
    {
        throw new static($message, $httpCode->value, $previous);
    }

    public static function fatal(string $message): never
    {
        throw new static($message, 700);
    }
}