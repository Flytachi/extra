<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Middleware;

use Flytachi\Extra\Src\Factory\Error\ExtraException;
use Flytachi\Extra\Src\Factory\Http\HttpCode;

class MiddlewareException extends ExtraException
{
    protected $code = HttpCode::UNAUTHORIZED->value;
}
