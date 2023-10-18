<?php

namespace Extra\Src\Process\Kube;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Process\ProcessException;

class KubeException extends ProcessException
{
    use ErrorLogTrait;
    protected string $handle = 'Process Kube';
}