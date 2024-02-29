<?php

namespace Extra\Src\Process\Core\Conductor;

use Extra\Src\Process\Kube\Prime\KubePrime;
use Extra\Src\Sheath\File\JSON;

class KubePrimeConductor implements Conductor
{
    public function recordAdd(string $className, int $pid): void
    {
         JSON::write(($className)::$STM_PATH ?? base64_encode($className),
            [
                'pid' => $pid,
                'title' => $className,
                'className' => $className,
                'condition' => 'started',
                'startedAt' => date('Y-m-d H:i:s P'),
                'info' => []
            ]
        );
    }

    public function recordRemove(string $className, int $pid): void
    {
        unlink(($className)::$STM_PATH ?? base64_encode($className));
    }
}