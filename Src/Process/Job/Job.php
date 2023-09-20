<?php

namespace Extra\Src\Process\Job;

use Extra\Src\Log\Log;
use Extra\Src\Process\Dispatcher\Dispatcher;
use Extra\Src\Process\Dispatcher\DispatcherInterface;

/**
 *  Warframe collection
 *
 *  Job
 *
 *  @version 1.0
 *  @author itachi
 *  @package Extra\Src
 */
abstract class Job extends Dispatcher implements JobInterface, DispatcherInterface
{
    /** @var bool $savePid Save TMP System process id */
    protected bool $savePid = false;
    /** @var int $pid System process id */
    protected int $pid;
    /** @var string $pidPath file path */
    private string $pidPath = PATH_APP . '/pid.json';

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
        $job = new static();
        $job->startRun();
        try {
            $job->run($data);
        } catch (\Throwable $e) {
            Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
        } finally {
            $job->endRun();
        }
        return $job->pid;
    }

    private function startRun(): void
    {
        $this->pid = getmypid();

        if ($this->savePid) {
            if (file_exists($this->pidPath)) {
                $data = json_decode(file_get_contents($this->pidPath), 1);
                $data['jobs'][$this->pid] = static::class;
                $jsonData = json_encode($data, JSON_PRETTY_PRINT);
                file_put_contents($this->pidPath, $jsonData);
            } else {
                $file = fopen($this->pidPath, "x");
                $data = ['jobs' => [$this->pid => static::class]];
                fwrite($file, json_encode($data, JSON_PRETTY_PRINT));
                chmod($this->pidPath, 0777);
            }
        }
    }

    private function endRun(): void
    {
        if ($this->savePid && file_exists($this->pidPath)) {
            $data = json_decode(file_get_contents($this->pidPath), 1);
            unset($data['jobs'][$this->pid]);
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents($this->pidPath, $jsonData);
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

}