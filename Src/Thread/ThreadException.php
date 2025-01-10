<?php

namespace Extra\Src\Thread;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;


/**
 * Base exception class for Process errors.
 */
class ThreadException extends ExtraException
{
    use ErrorLogTrait;
    protected  string $handle = 'Process';
}