<?php

namespace Extra\Src\Process\Caster;

interface CasterInterface
{
    public static function start(mixed $data = null): int;
    public function proc(int $pid, mixed $fragment = null): void;
    public function wait(?callable $callableEndChild = null): void;
}