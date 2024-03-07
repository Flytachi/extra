<?php

namespace Extra\Src\Process\Core\Dispatcher;

use Extra\Src\Log\Log;
use Extra\Src\Process\ProcessException;

/**
 * Class Dispatcher
 *
 * `Dispatcher` is an abstract class that provides a method to run a process in the background.
 * The class to execute and data to pass to the run process can be specified.
 *
 * The methods provided by `Dispatcher` include:
 *
 * - `runnable(mixed $data = null): int`: Executes a new process by the class from which this method is called.
 *   It takes data as a parameter if any, and returns the process ID of the created process.
 *
 * @version 3.0
 * @author Flytachi
 */
abstract class Dispatcher
{
    /**
     * Runs a process in the background by executing a command with given class name and data.
     *
     * @param mixed $data The data to be passed to the process. Default is null.
     * @return int The process ID of the spawned process.
     */
    protected final static function runnable(mixed $data = null): int
    {
        try {
            if ($data) {
                $fileName = uniqid("process-cache-");
                $filePath = PATH_CACHE . '/' . $fileName;
                $serializeData = serialize($data);
                file_put_contents($filePath, $serializeData);
                chmod($filePath, 0777);
                Log::trace('::' . static::class . ':: serialized => ' . $serializeData);
            }

            Log::trace('::' . static::class . ':: DISPATCH');
            return exec(sprintf(
                "php ../extra process run --class-name='%s' %s > %s 2>&1 & echo $!",
                static::class,
                ($data ? "--class-cache='{$fileName}'" : ''),
                "/dev/null"
            ));
        } catch (\Throwable $err) {
            ProcessException::fatal('::' . static::class . ':: ' . $err->getMessage());
        }

    }

}