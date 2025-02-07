<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Thread\Dispatcher;

/**
 * DispatcherInterface
 *
 * @method int    dispatch(mixed $data = null)
 */
interface DispatcherInterface
{
    public static function dispatch(mixed $data = null): int;
    public static function start(mixed $data = null): int;
    public function run(mixed $data = null): void;
}
