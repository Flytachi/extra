<?php

namespace Extra\Src\Process\Core\Dispatcher;


/**
 * DispatcherInterface
 *
 * @method int    dispatch(mixed $data = null)
 */
interface DispatcherInterface
{
    /**
     * @param mixed|null $data
     * @return int
     */
    public static function dispatch(mixed $data = null): int;

}