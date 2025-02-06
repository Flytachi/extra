<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Error;

use Flytachi\Extra\Extra;
use Flytachi\Extra\Src\Factory\Http\Header;
use Flytachi\Extra\Src\Factory\Http\HttpCode;
use JetBrains\PhpStorm\Pure;

abstract class ExtraException extends \Exception implements ExtraThrowable
{
    protected $code = HttpCode::INTERNAL_SERVER_ERROR->value;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        Extra::$logger->debug(sprintf(
            "Exception: %s\nStack trace:\n%s",
            $this->getMessage(),
            $this->getTraceAsString(),
        ));
    }


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
