<?php

namespace Extra\Src\Process\Job;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Process\ProcessException;

class JobException extends ProcessException
{
    use ErrorLogTrait;
    protected string $handle = 'Process Job';
}