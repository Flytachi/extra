<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Http;

use Flytachi\Extra\Extra;
use Flytachi\Extra\Src\Factory\Error\ExceptionWrapper;
use Flytachi\Extra\Src\Factory\Error\ExtraThrowable;
use Flytachi\Extra\Src\Factory\Http\Response\ResponseInterface;

class Rendering
{
    private HttpCode $httpCode;
    private array $header = [];
    private null|int|float|string $body;

    public function setResource(mixed $resource): void
    {
        if ($resource instanceof ResponseInterface) {
            $this->httpCode = $resource->getHttpCode();
            $this->header = $resource->getHeader();
            $this->body = $resource->getBody();
        } elseif ($resource instanceof \Throwable) {
            $this->httpCode = HttpCode::tryFrom($resource->getCode()) ?: HttpCode::INTERNAL_SERVER_ERROR;
            $this->logging($resource);
            if ($resource instanceof ExtraThrowable) {
                $this->header = $resource->getHeader();
                $this->body = $resource->getBody();
            } else {
                $this->header = ExceptionWrapper::wrapHeader();
                $this->body = ExceptionWrapper::wrapBody($resource);
            }
        } else {
            $this->httpCode = HttpCode::OK;
            $this->body = $resource;
        }
    }

    public function render(): never
    {
        Extra::$logger->withName(self::class)->debug(sprintf(
            "HTTP [%d] %s -> %s",
            $this->httpCode->value,
            $this->httpCode->message(),
            mb_substr($this->body, 0, 3000)
        ));
        header_remove("X-Powered-By");
        header("HTTP/1.1 {$this->httpCode->value} " . $this->httpCode->message());
        header("Status: {$this->httpCode->value} " . $this->httpCode->message());
        foreach ($this->header as $name => $value) {
            header("{$name}: {$value}");
        }
        echo $this->body;
        exit();
    }

    private function logging(\Throwable $resource): void
    {
        $typeError = $this->httpCode->isServerError()
            ? 'error'
            : ($this->httpCode->isClientError() ? 'warning' : 'emergency');
        Extra::$logger->withName($resource::class)->{$typeError}(sprintf(
            "%d: %s\n# %s(%d) -> Stack trace:\n%s",
            $resource->getCode(),
            $resource->getMessage(),
            $resource->getFile(),
            $resource->getLine(),
            $resource->getTraceAsString(),
        ));
    }
}
