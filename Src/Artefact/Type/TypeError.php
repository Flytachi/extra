<?php

namespace Extra\Src\Artefact\Type;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class TypeError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Type';
}