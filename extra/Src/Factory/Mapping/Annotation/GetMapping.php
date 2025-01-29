<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\Annotation;

use Attribute;
use Flytachi\Extra\Src\Factory\Mapping\MappingRequestInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class GetMapping extends AbstractMapping implements MappingRequestInterface
{
    protected ?string $call = 'GET';
}
