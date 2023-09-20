<?php

namespace Extra\Src\Route;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class RouteError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Route';
}