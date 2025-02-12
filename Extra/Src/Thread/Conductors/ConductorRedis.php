<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Thread\Conductors;

use Flytachi\Extra\Src\Factory\Connection\ConnectionPool;

/**
 * Class ConductorRedis
 *
 * This class implements the Conductor interface to provide
 * functionality for recording and removing records using Redis.
 *
 * @version 1.0
 * @author Flytachi
 */
class ConductorRedis implements Conductor
{
    public function recordAdd(string $className, int $pid): void
    {
        ConnectionPool::store('process-conductor')->set((string) $pid, $className);
    }

    public function recordRemove(string $className, int $pid): void
    {
        ConnectionPool::store('process-conductor')->del((string) $pid);
    }
}
