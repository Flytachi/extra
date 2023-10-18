<?php

namespace Extra\Src\Process\Kube;


use Extra\Src\Enum\HttpCode;
use Extra\Src\Log\Log;
use Extra\Src\Process\Conductor\ConductorInterface;
use Extra\Src\Process\Conductor\Json\Conductor;
use Extra\Src\Process\Dispatcher\Dispatcher;
use Extra\Src\Process\Dispatcher\DispatcherInterface;

/**
 *  Warframe collection
 *
 *  Kube
 *
 *  @version 1.0
 *  @author itachi
 *  @package Extra\Src\Process
 */
abstract class Kube extends Dispatcher implements KubeInterface, DispatcherInterface
{
    protected string $conductorClassName = Conductor::class;
    private ConductorInterface $conductor;
    /** @var int $pid Main system process id */
    protected int $pid;
    /** @var array<int> $childrenPid Children process ids */
    protected array $childrenPid = [];

    /**
     * Queue
     */
    private function __construct()
    {
        if (!is_dir(PATH_CACHE)) mkdir(PATH_CACHE, 0777, true);
    }

    /**
     * Start run script
     *
     * @param mixed|null $data
     * @return int
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
     * Father Process Prepare Start
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
     * Father Process Prepare End
     *
     * @return void
     */
    private function endRun(): void
    {
        if (PHP_SAPI === 'cli')
            $this->conductor->recordRemove(static::class, $this->pid);
    }

    protected function thread(callable $function): void
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

    protected function threadProc(mixed $data = null): void
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


    /**
     * @param null|callable $callableEndChild
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
        // Parent
        if (getmypid() === $this->pid) {
            foreach ($this->childrenPid as $childPid) {
                posix_kill($childPid, SIGHUP);
                pcntl_waitpid($childPid, $status);
            }
            $this->asClose();
            $this->endRun();
        }
        // Child
        else $this->asProcClose();
        exit(1);
    }

    /**
     * @return never
     */
    private function signInterrupt(): never
    {
        // Parent
        if (getmypid() === $this->pid) {
            foreach ($this->childrenPid as $childPid) {
                posix_kill($childPid, SIGINT);
                pcntl_waitpid($childPid, $status);
            }
            $this->asInterrupt();
            $this->endRun();
        }
        // Child
        else $this->asProcInterrupt();
        exit();
    }

    /**
     * @return never
     */
    private function signTermination(): never
    {
        // Parent
        if (getmypid() === $this->pid) {
            foreach ($this->childrenPid as $childPid) {
                posix_kill($childPid, SIGTERM);
                pcntl_waitpid($childPid, $status);
            }
            $this->asTermination();
            $this->endRun();
        }
        // Child
        else $this->asProcTermination();
        exit(1);
    }

    protected function asClose(): void
    {
        Log::critical("[{$this->pid}] " . static::class . ' CLOSE');
    }

    protected function asTermination(): void
    {
        Log::critical("[{$this->pid}] " . static::class . ' TERMINATION');
    }

    protected function asInterrupt(): void
    {
        Log::alert("[{$this->pid}] " . static::class . ' INTERRUPTED');
    }

    protected function asProcClose(): void
    {
        Log::critical('[' . getmypid() . '] ' . static::class . ' CLOSE CHILD');
    }

    protected function asProcTermination(): void
    {
        Log::critical('[' . getmypid() . '] ' . static::class . ' TERMINATION CHILD');
    }

    protected function asProcInterrupt(): void
    {
        Log::alert('[' . getmypid() . '] ' . static::class . ' INTERRUPTED CHILD');
    }

}