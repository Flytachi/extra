<?php

namespace Extra\Src\CDO;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class CDOError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'CDO';
}