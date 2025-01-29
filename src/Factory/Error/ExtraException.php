<?php

declare(strict_types=1);

namespace Flytachi\Extra\Factory\Error;

use Flytachi\Extra\Factory\Http\Header;
use Flytachi\Extra\Factory\Http\HttpCode;

abstract class ExtraException extends \Exception implements ExtraThrowable
{
    protected $code = HttpCode::INTERNAL_SERVER_ERROR->value;

    public function getHeader(): array
    {
        return ['Content-Type' => Header::getHeader('Accept')];
    }

    public function getBody(): string
    {
        return match (Header::getHeader('Accept')) {
            'application/json' => ExceptionWrapper::constructJson($this),
            'application/xml' => ExceptionWrapper::constructXml($this),
            default => ExceptionWrapper::constructDefault($this)
        };
    }
}
