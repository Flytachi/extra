<?php

namespace Extra\Src\Artefact\Redis;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class RedisError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Redis';
}