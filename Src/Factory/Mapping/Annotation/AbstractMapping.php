<?php

namespace Extra\Src\Factory\Mapping\Annotation;

use Attribute;
use Extra\Src\Factory\Mapping\Common\MappingRequestInterface;

#[Attribute]
abstract class AbstractMapping implements MappingRequestInterface
{
    protected string $call = 'request';
    protected string $url;

    /**
     * @param string $url HTTP URL
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getCallback(): string
    {
        return $this->call;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}