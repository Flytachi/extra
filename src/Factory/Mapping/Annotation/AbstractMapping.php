<?php

declare(strict_types=1);

namespace Flytachi\Extra\Factory\Mapping\Annotation;

use Flytachi\Extra\Factory\Mapping\MappingRequestInterface;

abstract class AbstractMapping implements MappingRequestInterface
{
    protected ?string $call = null;
    protected string $url;

    /**
     * @param string $url HTTP URL
     */
    public function __construct(string $url = '')
    {
        $this->url = $url;
    }

    public function getCallback(): string
    {
        return $this->call;
    }

    public function getUrl(): string
    {
        return trim($this->url, '/');
    }
}
