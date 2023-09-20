<?php

namespace Extra\Src\Repo;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class RepositoryError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Repository';
}