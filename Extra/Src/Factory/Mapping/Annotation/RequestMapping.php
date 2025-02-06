<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\Annotation;

use Attribute;
use Flytachi\Extra\Src\Factory\Mapping\MappingRequestInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RequestMapping extends AbstractMapping implements MappingRequestInterface
{
}
