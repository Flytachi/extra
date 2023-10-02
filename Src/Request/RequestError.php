<?php

namespace Extra\Src\Request;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class RequestError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Request';
}