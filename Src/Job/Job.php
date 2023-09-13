<?php

namespace Extra\Src\Job;

use Extra\Src\Logger;

/**
 *  Warframe collection
 *
 *  Job
 *
 *  @version 1.0
 *  @author itachi
 *  @package Extra\Src
 */
abstract class Job
{
    /** @var bool $savePid Save TMP System process id */
    protected bool $savePid = false;
    /** @var int $pid System process id */
    protected int $pid;
    /** @var string $pidPath file path */
    private string $pidPath = PATH_APP . '/pid.json';

    protected static JobLogger $log;

    public function __construct()
    {
        if (!is_dir(PATH_CACHE)) mkdir(PATH_CACHE, 0777, true);
        static::$log = new JobLogger(static::class);
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
            static::$log::error($e->getMessage() . "\n" . $e->getTraceAsString());
        } finally {
            $job->endRun();
        }
        return $job->pid;
    }

    /**
     * Start Job (async)
     *
     * Running a task in the background
     *
     * @param mixed|null $data
     *
     * @return int pid
     */
    public final static function dispatch(mixed $data = null): int
    {
        if ($data) {
            $fileName = uniqid("jobCache-");
            $filePath = PATH_CACHE . '/' . $fileName;
            file_put_contents($filePath, serialize($data));
            chmod($filePath, 0777);
        }
        return exec(sprintf(
            'php -q ../box job:run %s %s > %s 2>&1 & echo $!',
            str_replace('\\', '\\\\', static::class),
            (($data) ? $fileName:''),
            "/dev/null"
        ));
    }

    /**
     * Task
     *
     * Body task
     *
     * @param mixed|null $data
     *
     * @return void
     */
    protected function run(mixed $data = null): void
    {
        static::$log::info("RUN [" . $this->pid . "]");
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
        static::$log::info("START [" . $this->pid . "]");
    }

    private function endRun(): void
    {
        if ($this->savePid && file_exists($this->pidPath)) {
            $data = json_decode(file_get_contents($this->pidPath), 1);
            unset($data['jobs'][$this->pid]);
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents($this->pidPath, $jsonData);
        }
        static::$log::info("END [" . $this->pid . "]");
    }

}