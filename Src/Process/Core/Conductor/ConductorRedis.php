<?php

namespace Extra\Src\Process\Core\Conductor;

use Extra\Src\Artefact\Aegis;

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
        Aegis::store('kube-conductor')->set($pid, $className);
    }

    public function recordRemove(string $className, int $pid): void
    {
        Aegis::store('kube-conductor')->del($pid);
    }
}