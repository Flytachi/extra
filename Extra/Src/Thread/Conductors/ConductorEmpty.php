<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Thread\Conductors;

/**
 * Class ConductorEmpty
 *
 * This class is an empty implementation of the Conductor interface.
 * It does not perform any actions in the recordAdd and recordRemove methods.
 *
 * @version 2.0
 * @author Flytachi
 */
class ConductorEmpty implements Conductor
{
    public function recordAdd(string $className, int $pid): void
    {
    }

    public function recordRemove(string $className, int $pid): void
    {
    }
}
