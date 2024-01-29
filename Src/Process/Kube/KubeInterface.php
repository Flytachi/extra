<?php

namespace Extra\Src\Process\Kube;

interface KubeInterface
{
    public static function start(mixed $data = null): int;
    public function run(mixed $data = null): void;
    public function proc(int $pid, mixed $data = null): void;
    public function waitAll(?callable $callableEndChild = null): void;
    public function wait(int $pid, ?callable $callableEndChild = null): void;
}