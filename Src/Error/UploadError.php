<?php

namespace Extra\Src\Error;

class UploadError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Upload';
}