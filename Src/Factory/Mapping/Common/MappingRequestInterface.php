<?php

namespace Extra\Src\Factory\Mapping\Common;

interface MappingRequestInterface
{
    public function __construct(string $url);
    public function getCallback(): string;
    public function getUrl(): string;
    public function getTitle(): string;
}