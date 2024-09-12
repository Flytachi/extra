<?php

namespace Extra\Src\Factory\Entity;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class EntityError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Entity';
}