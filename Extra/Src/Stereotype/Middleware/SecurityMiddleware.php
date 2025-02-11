<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Stereotype\Middleware;

use Flytachi\Extra\Src\Factory\Http\Header;
use Flytachi\Extra\Src\Factory\Middleware\AbstractMiddleware;
use Flytachi\Extra\Src\Factory\Middleware\Cors\AccessControlMiddleware;
use Flytachi\Extra\Src\Factory\Middleware\MiddlewareException;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class SecurityMiddleware extends AbstractMiddleware
{
    public function optionBefore(): void
    {
        if (Header::getHeader('Authorization') == '') {
            throw new MiddlewareException('Authorization is empty');
        }
    }
}
