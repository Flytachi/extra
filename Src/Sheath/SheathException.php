<?php

namespace Extra\Src\Sheath;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class SheathException extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Sheath Exception';
}