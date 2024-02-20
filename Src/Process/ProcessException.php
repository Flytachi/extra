<?php

namespace Extra\Src\Process;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;


/**
 * Base exception class for Process errors.
 */
abstract class ProcessException extends ExtraException
{
    use ErrorLogTrait;
    protected  string $handle = 'Process';
}