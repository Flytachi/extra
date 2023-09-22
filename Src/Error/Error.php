<?php

namespace Extra\Src\Error;

class Error extends ExtraException
{
    use ErrorLogTrait;
    protected  string $handle = 'Exception';
}