<?php

namespace Extra\Src\Process\Kube;

interface KubeInterface
{
    public static function start(mixed $data = null): int;
    public function run(mixed $data = null): void;
    public function proc(int $pid, mixed $data = null): void;
    public function wait(?callable $callableEndChild = null): void;
}