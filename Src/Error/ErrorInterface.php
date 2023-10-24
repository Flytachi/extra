<?php

namespace Extra\Src\Error;

use Extra\Src\Enum\HttpCode;

interface ErrorInterface extends \Throwable
{
    public static function throw(HttpCode $httpCode, string $message, \Throwable|null $previous = null): never;
}