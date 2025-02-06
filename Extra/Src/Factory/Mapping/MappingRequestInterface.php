<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping;

interface MappingRequestInterface
{
    public function __construct(string $url = '');
    public function getCallback(): ?string;
    public function getUrl(): string;
}
