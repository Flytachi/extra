<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Thread\Conductors;

use Flytachi\Extra\Src\Unit\File\JSON;

class ConductorClusterJson implements Conductor
{
    public function recordAdd(string $className, int $pid): void
    {
        JSON::write(
            ($className)::$STM_PATH ?? base64_encode($className),
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
