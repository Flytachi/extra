<?php

namespace Extra\Src\Error;

use Extra\Src\Factory\Entity\Request\Request;
use Extra\Src\HttpCode;
use Extra\Src\Log\Log;

class BaseError extends ExtraException
{
    protected string $handle = 'Core';

    public final static function throw(HttpCode $httpCode, string $message, \Throwable|null $previous = null): never
    {
        $exception = new self($message, $httpCode->value);

        $logMessage = env('DEBUG', false) ?  ($message . "\n" . $exception->getTraceAsString()) : $message;
        Log::critical($logMessage);

        if (PHP_SAPI === 'cli') throw $exception;
        else {
            if (Request::inHeader('Accept', 'application/json')) die($exception->getThrowableJson());
            else die($exception->getThrowableText());
        }
    }

}