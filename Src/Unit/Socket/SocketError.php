<?php

namespace Extra\Src\Unit\Socket;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class SocketError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Socket';
}