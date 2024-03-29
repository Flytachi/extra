<?php

namespace Extra\Src\Unit\Blink;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class BlinkError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Blink';
}