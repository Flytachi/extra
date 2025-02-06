<?php

namespace Flytachi\Extra\Src\Stereotype;

use Flytachi\Extra\Src\Factory\Connection\Repository\Interfaces\RepositoryCrudInterface;
use Flytachi\Extra\Src\Factory\Connection\Repository\RepositoryCore;
use Flytachi\Extra\Src\Factory\Connection\Repository\Traits\RepositoryCrudTrait;

abstract class RepositoryCrud extends RepositoryCore implements RepositoryCrudInterface
{
    use RepositoryCrudTrait;
}
