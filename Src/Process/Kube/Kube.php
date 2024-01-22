<?php

namespace Extra\Src\Process\Kube;


use Extra\Src\Log\Log;
use Extra\Src\Process\Core\Conductor\ConductorEmpty;
use Extra\Src\Process\Core\Conductor\Conductor;
use Extra\Src\Process\Core\Dispatcher\Dispatcher;
use Extra\Src\Process\Core\Dispatcher\DispatcherInterface;
use Extra\Src\Process\PosixSignal;

/**
 * Class Kube
 *
 * `Kube` is an abstract class extending `Dispatcher`. It's designed to run tasks with methods for thread management,
 * process handling, and signal handling.
 * It implements interfaces `KubeInterface` and `DispatcherInterface` and uses traits `KubeSig` and `PosixSignal`.
 *
 * It also has a ConductorClass instance to manage job tasks. Each task runs in its process with `pid`.
 * Child processes spawned through threads are tracked in `childrenPid`.
 *
 * The methods provided by `Kube` include:
 *
 * - `start(mixed $data = null): int`: Starts the process with the given data.
 * - `thread(callable $function): void`: Creates a new thread for the provided function.
 * - `threadProc(mixed $data = null): void`: Creates new process for the provided data.
 * - `__construct()`: Constructor that checks and creates the cache directory if not exists.
 *
 * Additionally, `startRun()`,`endRun()` are private methods to manage processes and signal
 * handling which get called during the start and end stages of a process.
 *
 * @version 1.0
 * @author Flytachi
 */
abstract class Kube extends Dispatcher implements KubeInterface, DispatcherInterface
{
    use KubeHandler, PosixSignal;

    protected string $conductorClassName = ConductorEmpty::class;
    private Conductor $conductor;
    /** @var int $pid Main system process id */
    protected int $pid;
    /** @var array<int> $childrenPid Children process ids */
    protected array $childrenPid = [];

    private function __construct()
    {
        if (!is_dir(PATH_CACHE)) mkdir(PATH_CACHE, 0777, true);
    }

    /**
     * Starts the process with the given data.
     *
     * @param mixed $data The data to be processed. Default is null.
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
     * This method sets the process ID (pid) to the current process.
     * If the current SAPI is CLI, it registers signal handlers for SIGHUP, SIGINT, and SIGTERM signals.
     * It also sets the process title to the basename of the PATH_ROOT concatenated with the class name and 'Father'.
     * Finally, it adds the current class and pid to the conductor's record.
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
            cli_set_process_title(basename(PATH_ROOT) . ' ' . static::class . ' Father');
            $this->conductor->recordAdd(static::class, $this->pid);
        }
    }

    /**
     * Ends the execution of the current process.
     *
     * This method is called to end the execution of the current process. If the PHP server SAPI is "cli",
     * it will remove the record of the current class and process ID from the conductor.
     *
     * @return void
     */
    private function endRun(): void
    {
        if (PHP_SAPI === 'cli')
            $this->conductor->recordRemove(static::class, $this->pid);
    }

    /**
     * Executes the given function in a separate child process using fork.
     *
     * @param callable $function The function to be executed in the child process.
     *
     * @return void
     */
    protected final function thread(callable $function): void
    {
        try {
            $pid = pcntl_fork();
            if ($pid == -1) KubeException::fatal("[{$this->pid}] Error: Unable to fork process.");
            // Child process
            elseif ($pid == 0) {
                try {
                    $pid = getmypid();
                    if (PHP_SAPI === 'cli')
                        cli_set_process_title(basename(PATH_ROOT) . ' ' . static::class . ' Child');
                    $function();
                } catch (\Throwable $exception) {
                    Log::error("[$pid]: " .$exception->getMessage() . "\n" . $exception->getTraceAsString());
                } finally {
                    exit(0);
                }
            }
            // Parent process
            else $this->childrenPid[] = $pid;
        } catch (\Throwable $e) {
            Log::critical($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    /**
     * Executes the code in a separate thread by forking the process.
     *
     * @param mixed $data The data to be passed to the thread. Default is null.
     * @return void
     */
    protected final function threadProc(mixed $data = null): void
    {
        try {
            $pid = pcntl_fork();
            if ($pid == -1) KubeException::fatal("[{$this->pid}] Error: Unable to fork process.");
            // Child process
            elseif ($pid == 0) {
                try {
                    $pid = getmypid();
                    if (PHP_SAPI === 'cli')
                        cli_set_process_title(basename(PATH_ROOT) . ' ' . static::class . ' Child');
                    $this->proc(getmypid(), $data);
                } catch (\Throwable $exception) {
                    Log::error("[$pid]: " .$exception->getMessage() . "\n" . $exception->getTraceAsString());
                } finally {
                    exit(0);
                }
            }
            // Parent process
            else $this->childrenPid[] = $pid;
        } catch (\Throwable $e) {
            Log::critical($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function proc(int $pid, mixed $data = null): void
    {
        Log::info("PROC {$pid} running");
    }

    /**
     * Waits for child processes to finish execution.
     *
     * @param callable|null $callableEndChild Optional. A callback function that will be called with the child process ID and status after it finishes execution. Default is null.
     * @return void
     */
    public final function wait(?callable $callableEndChild = null): void
    {
        if (PHP_SAPI === 'cli') {
            foreach ($this->childrenPid as $key => $pid) {
                pcntl_waitpid($pid, $status);
                if (!is_null($callableEndChild)) $callableEndChild($pid, $status);
            }
        }
    }

    /**
     * Dispatches the given data to the runnable method.
     *
     * @param mixed $data The data to be dispatched. Default is null.
     * @return int The result returned by the runnable method.
     */
    public final static function dispatch(mixed $data = null): int
    {
        return self::runnable($data);
    }

}