<?php

namespace Extra\Src\Factory\Mapping\Annotation;

use Attribute;
use Extra\Src\Factory\Mapping\Common\MappingRequestInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class GetMapping extends AbstractMapping implements MappingRequestInterface
{
    protected string $call = 'get';
}