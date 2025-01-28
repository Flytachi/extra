<?php

declare(strict_types=1);

namespace Flytachi\Extra\Factory\Mapping\Annotation;

use Attribute;
use Flytachi\Extra\Factory\Mapping\MappingRequestInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class PostMapping extends AbstractMapping implements MappingRequestInterface
{
    protected ?string $call = 'POST';
}
