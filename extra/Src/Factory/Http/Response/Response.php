<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Http\Response;

use Flytachi\Extra\Src\Factory\Http\Header;
use Flytachi\Extra\Src\Factory\Http\HttpCode;
use Flytachi\Extra\Src\Unit\File\XML;

class Response implements ResponseInterface
{
    protected mixed $content;
    protected HttpCode $httpCode;

    public function __construct(mixed $content, mixed $httpCode = HttpCode::OK)
    {
        $this->content = $content;
        $this->httpCode = $httpCode;
    }

    final public function getHttpCode(): HttpCode
    {
        return $this->httpCode;
    }

    public function getHeader(): array
    {
        return ['Content-Type' => Header::getHeader('Accept')];
    }

    public function getBody(): string
    {
        return match (Header::getHeader('Accept')) {
            'application/json' => $this->constructJson($this->content),
            'application/xml' => $this->constructXml($this->content),
            default => $this->constructDefault($this->content)
        };
    }

    protected function constructJson(mixed $content): string
    {
        return json_encode($content);
    }

    protected function constructXml(mixed $content): string
    {
        if (is_array($content)) {
            return XML::arrayToXml($content);
        } elseif (is_object($content) || $content instanceof \stdClass) {
            return XML::arrayToXml(
                json_decode(json_encode($content), true)
            );
        } else {
            return XML::arrayToXml([$content]);
        }
    }

    protected function constructDefault(mixed $content): string
    {
        if (is_string($content) || is_numeric($content) || is_bool($content) || is_null($content)) {
            return (string) $content;
        } else {
            return print_r($content, true);
        }
    }
}
