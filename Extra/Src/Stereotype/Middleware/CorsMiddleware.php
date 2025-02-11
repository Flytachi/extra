<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Stereotype\Middleware;

use Flytachi\Extra\Src\Factory\Middleware\Cors\AccessControlMiddleware;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class CorsMiddleware extends AccessControlMiddleware
{
    public function optionBefore(): void
    {
    }
}
