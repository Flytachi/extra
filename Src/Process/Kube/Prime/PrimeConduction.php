<?php

namespace Extra\Src\Process\Kube\Prime;

use Extra\Src\Log\Log;
use Extra\Src\Sheath\File\JSON;

trait PrimeConduction
{
    protected final function stmPrepare(int $balancer = 10): void
    {
        // start
        $data = JSON::read(static::$STM_PATH);
        $data['condition'] = 'preparation';
        JSON::write(static::$STM_PATH, $data);
        Log::trace('::' . static::class . ':: set condition => preparation');

        // preparation
        $pathThreads = static::$STM_THREADS_PATH;
        if (!is_dir($pathThreads)) mkdir($pathThreads);
        $data['balancer'] = $balancer;
        $this->balancer = $balancer;
        JSON::write(static::$STM_PATH, $data);
        // custom
        $this->stmPreparing();

        // end
        $data = JSON::read(static::$STM_PATH);
        $data['condition'] = 'active';
        JSON::write(static::$STM_PATH, $data);
        Log::trace('::' . static::class . ':: set condition => active');
    }

    protected function stmPreparing(): void {}

    protected final function setCondition(string $newCondition): void
    {
        $data = JSON::read(static::$STM_PATH);
        $data['condition'] = $newCondition;
        JSON::write(static::$STM_PATH, $data);
        Log::trace('::' . static::class . ':: set condition => ' . $newCondition);
    }

    protected final function setInfo(array $newInfo): void
    {
        $data = JSON::read(static::$STM_PATH);
        $data['info'] = $newInfo;
        JSON::write(static::$STM_PATH, $data);
        Log::trace('::' . static::class . ':: set info => ' . json_encode($data));
    }
}