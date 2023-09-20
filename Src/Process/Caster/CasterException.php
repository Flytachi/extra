<?php

namespace Extra\Src\Process\Caster;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Process\ProcessException;

class CasterException extends ProcessException
{
    use ErrorLogTrait;
    protected string $handle = 'Process Caster';
}