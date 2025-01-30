<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Entity;

interface ModelInterface
{
    public function __construct();
    public static function selection(): array;
}
