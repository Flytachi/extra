<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Mapping\OpenApi\Schema;

interface Spl
{
    public function modify(array &$path): void;
}
