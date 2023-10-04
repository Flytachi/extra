<?php

namespace Extra\Src\Process\Dispatcher;

/**
 * Dispatcher
 *
 * @version 3.0
 */
abstract class Dispatcher
{

    /**
     * Runnable - start run in background
     *
     * @param mixed|null $data
     * @return int
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
                "php ../extra process run --class-name='%s' %s > %s 2>&1 & echo $!",
                static::class,
                ($data ? "--class-cache='{$fileName}'" : ''),
                "/dev/null"
            ));
        } catch (\Throwable $err) {
            DispatcherException::fatal($err->getMessage());
        }

    }
}