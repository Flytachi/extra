<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Middleware;

interface MiddlewareInterface
{
    public function optionBefore(): void;
    public function optionAfter(): void;
}
