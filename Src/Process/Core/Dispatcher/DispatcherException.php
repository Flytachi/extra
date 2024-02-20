<?php

namespace Extra\Src\Process\Core\Dispatcher;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Process\ProcessException;

class DispatcherException extends ProcessException
{
    use ErrorLogTrait;
    protected string $handle = 'Process Dispatcher';
}