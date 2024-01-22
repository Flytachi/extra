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
     * Starts the process by creating a new instance and running the necessary methods.
     *
     * @param mixed $data The data to be passed to the `run` method. Defaults to null if not provided.
     * @return int The process ID of the started process.
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

        if (PHP_SAPI === 'cli') {
            pcntl_signal(SIGHUP, function () {$this->signClose();});
            pcntl_signal(SIGINT, function () {$this->signInterrupt();});
            pcntl_signal(SIGTERM, function () {$this->signTermination();});
            cli_set_process_title(basename(PATH_ROOT) . ' ' . static::class);
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
        if (PHP_SAPI === 'cli')
            $this->conductor->recordRemove(static::class, $this->pid);
    }

    /**
     * Dispatches the given data to the `runnable` method and returns the result.
     *
     * @param mixed $data The data to be dispatched. Defaults to null if not provided.
     * @return int The result of the `runnable` method.
     */
    public final static function dispatch(mixed $data = null): int
    {
        return self::runnable($data);
    }

}