<?php

namespace Extra\Src\Unit\JWT;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class JWTError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'JWT';
}