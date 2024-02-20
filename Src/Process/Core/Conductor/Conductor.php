<?php

namespace Extra\Src\Process\Core\Conductor;

interface Conductor
{
    public function recordAdd(string $className, int $pid): void;
    public function recordRemove(string $className, int $pid): void;
}