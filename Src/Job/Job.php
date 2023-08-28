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
    /** @var int $pid System process id */
    protected int $pid;
    /** @var string $pidPath file path */
    private string $pidPath = PATH_APP . '/pid.json';

    /**
     * Start Job (sync)
     *
     * Running a task
     *
     * @return int pid
     */
    public static function start(): int
    {
        $job = new static();
        $job->startRun();
        $job->run();
        $job->endRun();
        return $job->pid;
    }

    /**
     * Start Job (async)
     *
     * Running a task in the background
     *
     * @return int pid
     */
    public static function dispatch(): int
    {
        return exec(sprintf(
            '%s > %s 2>&1 & echo $!',
            "php -q ../box job:run " . str_replace('\\', '\\\\', static::class),
            "/dev/null"
        ));
    }

    /**
     * Task
     *
     * Body task
     *
     * @return void
     */
    protected function run(): void
    {
        Logger::info("RUN Job [" . $this->pid . "] => ". self::class);
    }

    private function startRun(): void
    {
        $this->pid = getmypid();

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

    private function endRun(): void
    {
        if (file_exists($this->pidPath)) {
            $data = json_decode(file_get_contents($this->pidPath), 1);
            unset($data['jobs'][$this->pid]);
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents($this->pidPath, $jsonData);
        }
    }

}