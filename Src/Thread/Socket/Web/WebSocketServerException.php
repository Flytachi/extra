<?php

namespace Extra\Src\Thread\Socket\Web;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;


/**
 * Base exception class for Process errors.
 */
class WebSocketServerException extends ExtraException
{
    use ErrorLogTrait;
    protected  string $handle = 'WebSocketServer';
}