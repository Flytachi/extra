<?php

namespace Extra\Src\Error;

class RequestError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Request';
}