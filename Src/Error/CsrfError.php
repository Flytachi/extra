<?php

namespace Extra\Src\Error;

class CsrfError extends ExtraException
{
    use ErrorLogTrait;
    protected  string $handle = 'Csrf';
}