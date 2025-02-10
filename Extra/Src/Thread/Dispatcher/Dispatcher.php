<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Thread\Dispatcher;

use Flytachi\Extra\Extra;
use Flytachi\Extra\Src\Thread\ThreadException;
use Psr\Log\LoggerInterface;

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
    protected static ?LoggerInterface $logger = null;

    public function __construct()
    {
        if (!is_dir(Extra::$pathStorageCache)) {
            mkdir(Extra::$pathStorageCache, 0777, true);
        }
        set_time_limit(0);
        ob_implicit_flush();
    }

    /**
     * Runs a process in the background by executing a command with given class name and data.
     *
     * @param mixed $data The data to be passed to the process. Default is null.
     * @return int The process ID of the spawned process.
     * @throws ThreadException
     */
    final protected static function runnable(mixed $data = null): int
    {
        try {
            static::$logger = Extra::$logger->withName(static::class);
            if ($data) {
                $fileName = uniqid("process-cache-");
                $filePath = Extra::$pathStorageCache . '/' . $fileName;
                $serializeData = serialize($data);
                file_put_contents($filePath, $serializeData);
                chmod($filePath, 0777);
                self::$logger->debug('serialized => ' . $serializeData);
            }

            self::$logger->debug('DISPATCH');
            $selfDirectory = getcwd();
            chdir(Extra::$pathRoot);
            $pid = (int) exec(sprintf(
                "php extra run thread --class-name='%s' %s > %s 2>&1 & echo $!",
                static::class,
                ($data ? "--class-cache='{$fileName}'" : ''),
                "/dev/null"
            ));
            chdir($selfDirectory);
            return $pid;
        } catch (\Throwable $err) {
            throw new ThreadException($err->getMessage(), previous: $err);
        }
    }
}
