<?php

namespace Extra\Src\Thread\Kube\Prime;

use Extra\Src\Thread\Core\Conductor\KubePrimeConductor;
use Extra\Src\Thread\Kube\Kube;

/**
 * Class KubePrime
 *
 * @version 1.6
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
            cli_set_process_title('extra kube-process ' . static::class);
        $this->stmThreadBefore($pid);
    }

    protected function threadEndRun(int $pid): void
    {
        $this->stmThreadAfter($pid);
    }

    protected final function streaming(callable $complianceCallable, ?callable $negationCallable = null): void
    {
        while (true) {
            if ($this->stmThreadCount() < $this->balancer) {
                $complianceCallable();
            } else {
                if ($negationCallable !== null) $negationCallable();
            }
            usleep( ($this->balancer < 1000 ? 1_000_000 / $this->balancer : 1000) );
        }
    }
}