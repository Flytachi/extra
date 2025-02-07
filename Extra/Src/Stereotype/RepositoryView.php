<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Stereotype;

use Flytachi\Extra\Src\Factory\Connection\Repository\Interfaces\RepositoryViewInterface;
use Flytachi\Extra\Src\Factory\Connection\Repository\RepositoryCore;
use Flytachi\Extra\Src\Factory\Connection\Repository\Traits\RepositoryViewTrait;

abstract class RepositoryView extends RepositoryCore implements RepositoryViewInterface
{
    use RepositoryViewTrait;
}
