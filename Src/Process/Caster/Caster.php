<?php

namespace Extra\Src\Process\Caster;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Model\ModelInterface;
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
        $cast = new static();
        $cast->mainProcPrepare();
        $cast->mainProcFork($data);
        return $cast->pid;
    }

    /**
     * Father Process Prepare
     *
     * @return void
     */
    private function mainProcPrepare(): void
    {
        $this->pid = getmypid();
        if (PHP_SAPI === 'cli')
            cli_set_process_title(basename(PATH_ROOT) . ' ' . static::class . ' Father');
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
                if ($pid== -1) CasterException::fatal("[{$this->pid}] Error: Unable to fork process.");
                // Child process
                elseif ($pid == 0) $this->procFork($fragment);
                // Parent process
                else $this->childrenPid[] = $pid;
            }
        } else {
            $pid = pcntl_fork();
            if ($pid== -1) CasterException::fatal("[{$this->pid}] Error: Unable to fork process.");
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

}