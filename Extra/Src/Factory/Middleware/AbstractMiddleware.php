<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Middleware;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    final public function __construct()
    {
    }
    abstract public function optionBefore(): void;
    public function optionAfter(): void
    {
    }
}
