<?php

namespace Extra\Src\Factory\Router;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class RouteError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Route';

    public function __toString(): string
    {
        die(parent::__toString());
    }

}