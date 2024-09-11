<?php

namespace Extra\Src\Factory\Mapping;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class MappingError extends ExtraException
{
    use ErrorLogTrait;
    protected  string $handle = 'Mapping';
}