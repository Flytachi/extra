<?php

namespace Extra\Src\Sheath\File;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class FileException extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'File Exception';
}