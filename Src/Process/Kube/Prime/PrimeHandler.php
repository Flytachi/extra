<?php

namespace Extra\Src\Process\Kube\Prime;

use Extra\Src\HttpCode;
use Extra\Src\Process\ProcessException;
use Extra\Src\Sheath\File\FileException;
use Extra\Src\Sheath\File\JSON;

trait PrimeHandler
{
    public static function stmStatus(): ?array
    {
        try {
            return JSON::read(static::$STM_PATH);
        } catch (FileException $e) {
            return null;
        }
    }

    public static function stmThreadList(): array
    {
        $files = glob(static::$STM_THREADS_PATH . '/*.json');
        foreach ($files as $key => $path) $files[$key] = basename($path, '.json');
        return $files;
    }

    public static function stmThreadQty(): int
    {
        return count(glob(static::$STM_THREADS_PATH . '/*.json'));
    }

    public static function stmStart(): int
    {
        $status = self::stmStatus();
        if ($status) ProcessException::throw(HttpCode::LOCKED, "Kube process already exist [PID:{$status['pid']}] ({$status['startedAt']})");
        else return self::dispatch();
    }

    public static function stmStop(): bool
    {
        $status = self::stmStatus();
        if ($status) return self::interrupt($status['pid']);
        else ProcessException::throw(HttpCode::LOCKED, "Kube process has not started");
    }
}