<?php

namespace Extra\Src\Thread\Kube;


use Extra\Src\Log\Log;
use Extra\Src\Thread\Core\Conductor\ConductorEmpty;
use Extra\Src\Thread\Core\Conductor\Conductor;
use Extra\Src\Thread\Core\Dispatcher\Dispatcher;
use Extra\Src\Thread\Core\Dispatcher\DispatcherInterface;
use Extra\Src\Thread\PosixSignal;
use Extra\Src\Thread\ThreadException;

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
 * @version 1.5
 * @author Flytachi
 */
abstract class Kube extends Dispatcher implements KubeInterface, DispatcherInterface
{
    use KubeHandler, PosixSignal;

    protected string $conductorClassName = ConductorEmpty::class;
    private Conductor $conductor;
    /** @var int $pid Main system process id */
    protected int $pid;
    /** @var bool $childrenPidSave Children process ids on/off */
    protected bool $childrenPidSave = true;
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
            Log::error('::' . static::class . ':: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
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
            cli_set_process_title('extra kube-process ' . static::class);
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
     * Executes a function in a separate child process using forking.
     *
     * @param callable $function The function to be executed in the child process.
     * @return int The process ID of the child process.
     */
    protected final function thread(callable $function): int
    {
        try {
            $pid = pcntl_fork();
            if ($pid == -1) ThreadException::fatal('::' . static::class . ":: [{$this->pid}] Error: Unable to fork process.");
            // Child process
            elseif ($pid == 0) {
                try {
                    $pid = getmypid();
                    $this->threadStartRun($pid);
                    try { $function(); }
                    catch (\Throwable $exception) {
                        Log::error('::' . static::class . ":: [$pid] Thread: Logic => " .$exception->getMessage() . "\n" . $exception->getTraceAsString());
                    }
                } catch (\Throwable $exception) {
                    Log::error('::' . static::class . ":: [$pid] Thread: " .$exception->getMessage() . "\n" . $exception->getTraceAsString());
                } finally {
                    $this->threadEndRun($pid);
                    exit(0);
                }
            }
            // Parent process
            else {
                if ($this->childrenPidSave) $this->childrenPid[] = $pid;
                return $pid;
            }
        } catch (\Throwable $e) {
            Log::critical('::' . static::class . ':: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return 0;
        }
    }

    /**
     * Executes the thread process by forking a new process and running the proc method.
     *
     * @param mixed $data The data to be passed to the proc method. Default is null.
     * @return int The PID (Process ID) of the child process if the process was successfully forked, otherwise null.
     */
    protected final function threadProc(mixed $data = null): int
    {
        try {
            $pid = pcntl_fork();
            if ($pid == -1) ThreadException::fatal('::' . static::class . ":: [{$this->pid}] Error: Unable to fork process.");
            // Child process
            elseif ($pid == 0) {
                try {
                    $pid = getmypid();
                    $this->threadStartRun($pid);
                    try { $this->proc($pid, $data); }
                    catch (\Throwable $exception) {
                        Log::error('::' . static::class . ":: [$pid] Thread(proc): Logic => " .$exception->getMessage() . "\n" . $exception->getTraceAsString());
                    }
                } catch (\Throwable $exception) {
                    Log::error('::' . static::class . ":: [$pid] Thread(proc): " .$exception->getMessage() . "\n" . $exception->getTraceAsString());
                } finally {
                    $this->threadEndRun($pid);
                    exit(0);
                }
            }
            // Parent process
            else {
                if ($this->childrenPidSave) $this->childrenPid[] = $pid;
                return $pid;
            }
        } catch (\Throwable $e) {
            Log::critical('::' . static::class . ':: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return 0;
        }
    }

    protected function threadStartRun(int $pid): void
    {
        if (PHP_SAPI === 'cli')
            cli_set_process_title('extra kube-thread ' . static::class);
    }

    protected function threadEndRun(int $pid): void {}

    public function proc(int $pid, mixed $data = null): void
    {
        Log::info('::' . static::class . ":: [{$pid}] -proc- running");
    }

    public final function wait(int $pid, ?callable $callableEndChild = null): void
    {
        if (PHP_SAPI === 'cli') {
            pcntl_waitpid($pid, $status);
            if (!is_null($callableEndChild)) $callableEndChild($pid, $status);
        }
    }

    /**
     * Waits for child processes to finish execution.
     *
     * @param callable|null $callableEndChild Optional. A callback function that will be called with the child process ID and status after it finishes execution. Default is null.
     * @return void
     */
    public final function waitAll(?callable $callableEndChild = null): void
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