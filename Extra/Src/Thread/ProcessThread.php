<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Thread;

use Flytachi\Extra\Extra;
use Flytachi\Extra\Src\Thread\Conductors\Conductor;
use Flytachi\Extra\Src\Thread\Conductors\ConductorEmpty;
use Flytachi\Extra\Src\Thread\Dispatcher\Dispatcher;
use Flytachi\Extra\Src\Thread\Dispatcher\DispatcherInterface;
use Flytachi\Extra\Src\Thread\Traits\ProcessHandler;
use Flytachi\Extra\Src\Thread\Traits\Thread;

abstract class ProcessThread extends Dispatcher implements DispatcherInterface
{
    use ProcessHandler;
    use Thread;

    protected string $conductorClassName = ConductorEmpty::class;
    private Conductor $conductor;
    /** @var int $pid System process id */
    protected int $pid;
    /** @var bool $childrenPidSave Children process ids on/off */
    protected bool $childrenPidSave = true;
    /** @var array<int> $childrenPid Children process ids */
    protected array $childrenPid = [];

    public static function start(mixed $data = null): int
    {
        $process = new static();

        try {
            $process->conductor = new $process->conductorClassName();
            $process->startRun();
            $process->run($data);
        } catch (\Throwable $e) {
            static::$logger->error($e->getMessage());
        } finally {
            $process->endRun();
        }
        return $process->pid;
    }

    /**
     * Starts the run process.
     *
     * This method sets the current process ID, registers signal handlers for SIGHUP, SIGINT, and SIGTERM,
     * sets the process title for CLI, and adds the current class to the conductor's record.
     *
     * @return void
     */
    private function startRun(): void
    {
        $this->pid = getmypid();
        static::$logger = Extra::$logger->withName("[{$this->pid}] " . static::class);

        if (PHP_SAPI === 'cli') {
            pcntl_signal(SIGHUP, function () {
                $this->signClose();
            });
            pcntl_signal(SIGINT, function () {
                $this->signInterrupt();
            });
            pcntl_signal(SIGTERM, function () {
                $this->signTermination();
            });
            cli_set_process_title('extra process ' . static::class);
            $this->conductor->recordAdd(static::class, $this->pid);
        }
    }

    /**
     * Ends the execution of the run method.
     *
     * This method is responsible for performing any necessary clean-up tasks
     * after the run method finishes executing. If the PHP SAPI (Server Application
     * Programming Interface) is 'cli' (Command Line Interface), it records the
     * removal of the class and its process ID ($pid) to the conductor.
     *
     * @return void
     */
    private function endRun(): void
    {
        if (PHP_SAPI === 'cli') {
            $this->conductor->recordRemove(static::class, $this->pid);
        }
    }

    final public function wait(int $pid, ?callable $callableEndChild = null): void
    {
        if (PHP_SAPI === 'cli') {
            pcntl_waitpid($pid, $status);
            if (!is_null($callableEndChild)) {
                $callableEndChild($pid, $status);
            }
        }
    }

    /**
     * Waits for child processes to finish execution.
     *
     * @param callable|null $callableEndChild Optional. A callback function that will be called
     * with the child process ID and status after it finishes execution. Default is null.
     * @return void
     */
    final public function waitAll(?callable $callableEndChild = null): void
    {
        if (PHP_SAPI === 'cli') {
            foreach ($this->childrenPid as $key => $pid) {
                pcntl_waitpid($pid, $status);
                if (!is_null($callableEndChild)) {
                    $callableEndChild($pid, $status);
                }
            }
        }
    }

    /**
     * @throws ThreadException
     */
    final public static function dispatch(mixed $data = null): int
    {
        return self::runnable($data);
    }
}
