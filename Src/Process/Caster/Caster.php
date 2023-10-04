<?php

namespace Extra\Src\Process\Caster;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Log\Log;
use Extra\Src\Model\ModelInterface;
use Extra\Src\Process\Conductor\ConductorInterface;
use Extra\Src\Process\Conductor\Json\Conductor;
use Extra\Src\Process\Dispatcher\Dispatcher;
use Extra\Src\Process\Dispatcher\DispatcherInterface;

/**
 *  Warframe collection
 *
 *  Caster
 *
 *  @version 1.0
 *  @author itachi
 *  @package Extra\Src\Process
 */
abstract class Caster extends Dispatcher implements CasterInterface, DispatcherInterface
{
    protected string $conductorClassName = Conductor::class;
    private ConductorInterface $conductor;
    /** @var int $pid Main system process id */
    protected int $pid;
    /** @var array<int> $childrenPid Children process ids */
    protected  array $childrenPid;
    /** @var int $workerQty Count workers */
    protected int $workerQty = 1;

    /**
     * Caster
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
            $process->mainProcPrepareStart();
            $process->mainProcFork($data);
        } catch (\Throwable $e) {
            Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
        } finally {
            $process->mainProcPrepareEnd();
        }

        return $process->pid;
    }

    /**
     * Father Process Prepare Start
     *
     * @return void
     */
    private function mainProcPrepareStart(): void
    {
        $this->pid = getmypid();
        pcntl_signal(SIGHUP, function () {$this->signClose();});
        pcntl_signal(SIGINT, function () {$this->signInterrupt();});
        pcntl_signal(SIGTERM, function () {$this->signTermination();});

        if (PHP_SAPI === 'cli')
            cli_set_process_title(basename(PATH_ROOT) . ' ' . static::class . ' Father');

        $this->conductor->recordAdd(static::class, $this->pid);
    }

    /**
     * Father Process Prepare End
     *
     * @return void
     */
    private function mainProcPrepareEnd(): void
    {
        $this->conductor->recordRemove(static::class, $this->pid);
    }

    /**
     * Father Process Fork
     *
     * @param mixed|null $data
     * @return void
     */
    private function mainProcFork(mixed $data = null): void
    {
        // Father Before method
        try {
            $this->mainBefore($data);
        } catch (\Throwable $err) {
            CasterException::fatal("MainBefore: " . $err->getMessage());
        }

        // Father Fork
        if (is_array($data)) {
            foreach ($data as $fragment) {
                $pid = pcntl_fork();
                if ($pid == -1) CasterException::fatal("[{$this->pid}] Error: Unable to fork process.");
                // Child process
                elseif ($pid == 0) $this->procFork($fragment);
                // Parent process
                else $this->childrenPid[] = $pid;
            }
        } else {
            $pid = pcntl_fork();
            if ($pid == -1) CasterException::fatal("[{$this->pid}] Error: Unable to fork process.");
            // Child process
            elseif ($pid == 0) $this->procFork($data);
            // Parent process
            else $this->childrenPid[] = $pid;
        }

        // Father After method
        try {
            $this->mainAfter();
        } catch (\Throwable $err) {
            CasterException::fatal("MainAfter: " . $err->getMessage());
        }
    }

    /**
     * Child Process Fork
     *
     * @param mixed|null $data
     * @return void
     */
    private function procFork(mixed $data = null): void
    {
        try {
            $pid = getmypid();
            if (PHP_SAPI === 'cli')
                cli_set_process_title(basename(PATH_ROOT) . ' ' . static::class . ' Child');
            $this->proc(getmypid(), $data);
        } catch (\Throwable $err) {
            CasterException::throw(HttpCode::INTERNAL_SERVER_ERROR,
                "[{$pid}] " . $err->getMessage() . "\n" . $err->getTraceAsString()
            );
        } finally {
            exit();
        }
    }

    /**
     * Sorting Array Data
     *
     * @param array<array|ModelInterface> $listElements
     * @return array
     */
    private function listSort(array $listElements): array
    {
        $list = [];
        $count = count($listElements);
        for ($i = 0; $i < $count; $i++)
            $list[$i % $this->workerQty][] = $listElements[$i];
        return $list;
    }

    /**
     * @param null|callable $callableEndChild
     * @return void
     */
    public final function wait(?callable $callableEndChild = null): void
    {
        foreach ($this->childrenPid as $pid) {
            pcntl_waitpid($pid, $status);
            if (!is_null($callableEndChild)) $callableEndChild($pid, $status);
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
     * Father Before Body
     *
     * @param mixed $data
     * @return void
     */
    protected function mainBefore(mixed &$data): void
    {
        if (is_array($data))
            $data = $this->listSort($data);
    }

    /**
     * Father After Body
     *
     * @return void
     */
    protected function mainAfter(): void {}


    /**
     * @return never
     */
    private function signClose(): never
    {
        // Parent
        if (getmypid() === $this->pid) {
            foreach ($this->childrenPid as $childPid) {
                posix_kill($childPid, SIGINT);
                pcntl_waitpid($childPid, $status);
            }
            $this->asClose();
            $this->mainProcPrepareEnd();
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
            $this->mainProcPrepareEnd();
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
            $this->mainProcPrepareEnd();
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
        Log::critical('[' . getmypid() . '] ' . static::class . ' TERMINATION CLOSE');
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