<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Stereotype;

use Flytachi\Extra\Src\Factory\Connection\Repository\Interfaces\RepositoryCrudInterface;
use Flytachi\Extra\Src\Factory\Connection\Repository\Interfaces\RepositoryViewInterface;
use Flytachi\Extra\Src\Factory\Connection\Repository\RepositoryCore;
use Flytachi\Extra\Src\Factory\Connection\Repository\Traits\RepositoryCrudTrait;
use Flytachi\Extra\Src\Factory\Connection\Repository\Traits\RepositoryViewTrait;

abstract class Repository extends RepositoryCore implements RepositoryCrudInterface, RepositoryViewInterface
{
    use RepositoryCrudTrait;
    use RepositoryViewTrait;
}
