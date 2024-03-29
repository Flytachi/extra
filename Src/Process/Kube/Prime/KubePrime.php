<?php

namespace Extra\Src\Process\Kube\Prime;

use Extra\Src\Process\Core\Conductor\KubePrimeConductor;
use Extra\Src\Process\Kube\Kube;

/**
 * Class KubePrime
 *
 * @version 1.0
 * @author Flytachi
 */
abstract class KubePrime extends Kube
{
    use PrimeConduction, PrimeDb, PrimeHandler;
    public static string $STM_PATH = PATH_CACHE . '/pKube.json';
    public static string $STM_THREADS_PATH = PATH_CACHE . '/pKubeThreads';
    protected string $conductorClassName = KubePrimeConductor::class;
    protected bool $childrenPidSave = false;
    protected int $balancer = 10;

    protected function asProcInterrupt(): void
    {
        $this->stmThreadAfter(getmypid());
        parent::asProcInterrupt();
    }

    protected function threadStartRun(int $pid): void
    {
        if (PHP_SAPI === 'cli')
            cli_set_process_title(basename(PATH_ROOT) . ' ' . static::class . ' thread');
        $this->stmThreadBefore($pid);
    }

    protected function threadEndRun(int $pid): void
    {
        $this->stmThreadAfter($pid);
    }
}