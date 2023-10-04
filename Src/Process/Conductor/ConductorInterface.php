<?php

namespace Extra\Src\Process\Conductor;

interface ConductorInterface
{
    public function recordAdd(string $className, int $pid): void;
    public function recordRemove(string $className, int $pid): void;
}