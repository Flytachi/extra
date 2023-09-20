<?php

namespace Extra\Src\Artefact;

use Extra\Src\Error\ErrorLogTrait;
use Extra\Src\Error\ExtraException;

class ArtefactError extends ExtraException
{
    use ErrorLogTrait;
    protected string $handle = 'Artefact';
}