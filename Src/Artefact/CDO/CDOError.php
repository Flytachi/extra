<?php

namespace Extra\Src\Artefact\CDO;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class CDOError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'CDO';
}