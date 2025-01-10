<?php

namespace Extra\Src\Factory\Mapping\OpenApi;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class OpenApiError extends ExtraException
{
    use ErrorLogTrait;
    protected  string $handle = 'OpenApiError';
}