<?php

namespace Extra\Src\Process\Dispatcher;

/**
 * Dispatcher
 *
 * @version 1.0
 */
abstract class Dispatcher
{

    /**
     * Runnable - start run in background
     *
     * @param mixed|null $data
     * @return int
     * @throws DispatcherException
     */
    protected final static function runnable(mixed $data = null): int
    {
        try {
            if ($data) {
                $fileName = uniqid("cast-cache-");
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
        } catch (\Throwable $err) {
            throw new DispatcherException($err->getMessage());
        }

    }
}