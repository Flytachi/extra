<?php

namespace Extra\Src\Process\Queue;

use Extra\Src\Process\Caster\CasterInterface;
use Extra\Src\Process\Dispatcher\Dispatcher;
use Extra\Src\Process\Dispatcher\DispatcherException;
use Extra\Src\Process\Dispatcher\DispatcherInterface;

class Queue extends Dispatcher implements CasterInterface, DispatcherInterface
{
    public static function start(mixed $data = null): int
    {
        // TODO: Implement start() method.
    }

    public function proc(int $pid, mixed $fragment = null): void
    {
        // TODO: Implement proc() method.
    }

    public function wait(?callable $callableEndChild = null): void
    {
        // TODO: Implement wait() method.
    }

    /**
     * Dispatch script
     *
     * @param mixed|null $data
     * @return int
     */
    public final static function dispatch(mixed $data = null): int
    {
        try {
            return self::runnable($data);
        } catch (DispatcherException $err) {
//            static::$log::error($err->getMessage() . "\n" . $err->getTraceAsString());
        }
    }

}