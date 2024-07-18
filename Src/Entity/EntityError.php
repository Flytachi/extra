<?php

namespace Extra\Src\Entity;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class EntityError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Entity';
}