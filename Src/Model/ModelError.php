<?php

namespace Extra\Src\Model;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class ModelError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Model';
}