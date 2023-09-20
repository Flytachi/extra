<?php

namespace Extra\Src\Process\Queue;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Process\ProcessException;

class QueueException extends ProcessException
{
    use ErrorLogTrait;
    protected string $handle = 'Process Queue';
}