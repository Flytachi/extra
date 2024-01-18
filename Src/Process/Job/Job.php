<?php

namespace Extra\Src\Process\Job;

use Extra\Src\Log\Log;
use Extra\Src\Process\Core\Conductor\ConductorInterface;
use Extra\Src\Process\Core\Conductor\Json\Conductor;
use Extra\Src\Process\Core\Dispatcher\Dispatcher;
use Extra\Src\Process\Core\Dispatcher\DispatcherInterface;


/**
 * Class Job
 *
 * `Job` is an abstract class extending `Dispatcher`. It's designed to run tasks that take a long time, either in the foreground or background.
 * It provides a rich life cycle for jobs, including initialization, running, and termination, and interrupt processing.
 *
 * The methods provided by `Job` include:
 *
 * - `start(mixed $data = null): int`: Start the task (sync) provided by the data argument, returns the process ID of the task.
 * - `dispatch(mixed $data = null): int`: Dispatch the task in the background, returns the process ID of the task.
 * - `asClose(): void`: Defines what job has to do when it is asked to close.
 * - `asTermination(): void`: Defines what job has to do when it is asked to terminate.
 * - `asInterrupt(): void`: Defines what job has to do when it gets interrupted.
 *
 * The class also defines preparatory (`startRun()`) and tear-down (`endRun()`) routines to be executed when the job starts and ends, respectively.
 *
 * @version 1.8
 * @author Flytachi
 */
abstract class Job extends Dispatcher implements JobInterface, DispatcherInterface
{
    protected string $conductorClassName = Conductor::class;
    private ConductorInterface $conductor;
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

    /**
     * @return never
     */
    private function signClose(): never
    {
        $this->asClose();
        $this->endRun();
        exit(1);
    }

    /**
     * @return never
     */
    private function signInterrupt(): never
    {
        $this->asInterrupt();
        $this->endRun();
        exit();
    }


    /**
     * @return never
     */
    private function signTermination(): never
    {
        $this->asTermination();
        $this->endRun();
        exit(1);
    }

    protected function asClose(): void
    {
        Log::critical(static::class . ' CLOSE TERMINAL');
    }

    protected function asTermination(): void
    {
        Log::critical(static::class . ' TERMINATION');
    }

    protected function asInterrupt(): void
    {
        Log::alert(static::class . ' INTERRUPTED');
    }

}