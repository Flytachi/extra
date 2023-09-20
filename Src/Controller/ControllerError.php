<?php

namespace Extra\Src\Controller;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class ControllerError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Controller';
}