<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Http;

use Flytachi\Extra\Extra;
use Flytachi\Extra\Src\Factory\Error\ExceptionWrapper;
use Flytachi\Extra\Src\Factory\Error\ExtraThrowable;
use Flytachi\Extra\Src\Factory\Http\Response\ResponseInterface;
use Flytachi\Extra\Src\Factory\Http\Response\ViewInterface;

final class Rendering
{
    private HttpCode $httpCode;
    private array $header = [];
    private null|int|float|string|array $body;
    private ?string $resource = null;
    private ?string $handle = null;

    public function setResource(mixed $resource): void
    {
        if ($resource instanceof ResponseInterface) {
            $this->httpCode = $resource->getHttpCode();
            $this->header = $resource->getHeader();
            $this->body = $resource->getBody();
        } elseif ($resource instanceof ViewInterface) {
            $this->httpCode = $resource->getHttpCode();
            $this->header = $resource->getHeader();
            $this->resource = $resource->getResource();
            $this->body = $resource->getData();
            $this->handle = $resource->getHandle();
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
        header_remove("X-Powered-By");
        header("HTTP/1.1 {$this->httpCode->value} " . $this->httpCode->message());
        header("Status: {$this->httpCode->value} " . $this->httpCode->message());
        foreach ($this->header as $name => $value) {
            header("{$name}: {$value}");
        }
        if (!empty($this->handle)) {
            echo $this->handle;
        }
        if (!empty($this->resource)) {
            Extra::$logger->withName("Rendering")->debug(sprintf(
                "HTTP [%d] %s -> %s",
                $this->httpCode->value,
                $this->httpCode->message(),
                $this->resource
            ));
            $data = $this->body;
            if (is_array($data)) {
                extract($data);
            }
            include $this->resource;
        } else {
            Extra::$logger->withName("Rendering")->debug(sprintf(
                "HTTP [%d] %s -> %s",
                $this->httpCode->value,
                $this->httpCode->message(),
                mb_substr($this->body, 0, 3000)
            ));
            echo $this->body;
        }
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
