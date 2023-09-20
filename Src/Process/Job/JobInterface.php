<?php

namespace Extra\Src\Process\Job;

interface JobInterface
{
    public static function start(mixed $data = null): int;
    public function run(mixed $data = null): void;
}