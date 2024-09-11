<?php

namespace Extra\Src\Factory\Mapping\Annotation;

use Attribute;
use Extra\Src\Factory\Mapping\Common\MappingRequestInterface;

abstract class AbstractMapping implements MappingRequestInterface
{
    protected string $call = 'request';
    protected string $title;
    protected string $url;

    /**
     * @param string $url HTTP URL
     * @param string $title
     */
    public function __construct(string $url, string $title = '')
    {
        $this->url = $url;
        $this->title = $title;
    }

    public function getCallback(): string
    {
        return $this->call;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): string
    {
        return $this->title ?: $this->url;
    }
}