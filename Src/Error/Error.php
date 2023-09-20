<?php

namespace Extra\Src\Error;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\Request;
use Extra\Src\Log\Log;

class Error extends ExtraException
{
    protected string $handle = 'Core';

    public final static function throw(HttpCode $httpCode, string $message): never
    {
        $exception = new self($message, $httpCode->value);

        $logMessage = env('DEBUG', false) ?  ($message . "\n" . $exception->getTraceAsString()) : $message;
        Log::critical($logMessage);

        if (PHP_SAPI === 'cli') throw $exception;
        else {
            if (Request::getHeader('Accept') == 'application/json') die($exception->getThrowableJson());
            else die($exception->getThrowableText());
        }
    }

}