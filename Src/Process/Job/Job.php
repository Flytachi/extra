<?php

namespace Extra\Src\Process\Job;

use Extra\Src\Log\Log;
use Extra\Src\Process\Core\Conductor\ConductorEmpty;
use Extra\Src\Process\Core\Conductor\Conductor;
use Extra\Src\Process\Core\Dispatcher\Dispatcher;
use Extra\Src\Process\Core\Dispatcher\DispatcherInterface;
use Extra\Src\Process\PosixSignal;

/**
 * Class Job
 *
 * `Job` is an abstract class extending `Dispatcher`. It's designed to run tasks with methods to start, dispatch, and signal handling.
 * It implements interfaces `JobInterface` and `DispatcherInterface` and uses traits `JobSig` and `PosixSignal`.
 * It also has a ConductorClass instance to manage job tasks. Each task will run in its process with `pid`.
 *
 * The methods provided by `Job` include:
 *
 * - `start(mixed $data = null): int`: Start the task (sync). Running a task provided by the data argument, returns the process ID of the task.
 * - `dispatch(mixed $data = null): int`: Dispatch the task. Same as `start()`.
 *
 * The class also defines preparatory (`startRun()`) and tear-down (`endRun()`) private routines to manage the conductor and signal handling.
 *
 * @version 2.5
 * @author Flytachi
 */
abstract class Job extends Dispatcher implements JobInterface, DispatcherInterface
{
    use JobSig, PosixSignal;
    protected string $conductorClassName = ConductorEmpty::class;
    private Conductor $conductor;
    /** @var int $pid System process id */
    protected int $pid;

    public function __construct()
    {
        if (!is_dir(PATH_CACHE)) mkdir(PATH_CACHE, 0777, true);
    }

    /**
     * Start Job (sync)
     *
     * Running a task
     *
     * @param mixed|null $data
     *
     * @return int pid
     */
    public final static function start(mixed $data = null): int
    {
        $process = new static();

        try {
            $process->conductor = new $process->conductorClassName;
            $process->startRun();
            $process->run($data);
        } catch (\Throwable $e) {
            Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
        } finally {
            $process->endRun();
        }
        return $process->pid;
    }

    private function startRun(): void
    {
        $this->pid = getmypid();

        if (PHP_SAPI === 'cli') {
            pcntl_signal(SIGHUP, function () {$this->signClose();});
            pcntl_signal(SIGINT, function () {$this->signInterrupt();});
            pcntl_signal(SIGTERM, function () {$this->signTermination();});
            cli_set_process_title(basename(PATH_ROOT) . ' ' . static::class);
            $this->conductor->recordAdd(static::class, $this->pid);
        }
    }

    private function endRun(): void
    {
        if (PHP_SAPI === 'cli')
            $this->conductor->recordRemove(static::class, $this->pid);
    }

    /**
     * Dispatch script
     *
     * @param mixed|null $data
     * @return int
     */
    public final static function dispatch(mixed $data = null): int
    {
        return self::runnable($data);
    }

}