<?php

namespace Extra\Src\Thread\Kube\Prime;

use Extra\Src\Log\Log;
use Extra\Src\Sheath\File\JSON;

trait PrimeDb
{
    protected function stmThreadCount(): int
    {
        $files = glob(static::$STM_THREADS_PATH . "/*.json");
        return count($files);
    }

    protected function stmThreadBefore(int $pid): void
    {
        JSON::write(static::$STM_THREADS_PATH . "/{$pid}.json", [
            'pid' => $pid,
        ]);
        Log::trace('::' . static::class . ":: [{$pid}] => started");
    }

    protected function stmThreadAfter(int $pid): void
    {
        unlink(static::$STM_THREADS_PATH . "/{$pid}.json");
        Log::trace('::' . static::class . ":: [{$pid}] => finished");
    }


}