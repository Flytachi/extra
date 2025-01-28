<?php

declare(strict_types=1);

namespace Flytachi\Extra\Factory\Mapping\Annotation;

use Attribute;
use Flytachi\Extra\Factory\Mapping\MappingRequestInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RequestMapping extends AbstractMapping implements MappingRequestInterface
{
}
