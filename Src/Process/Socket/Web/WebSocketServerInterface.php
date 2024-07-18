<?php

namespace Extra\Src\Process\Socket\Web;

interface WebSocketServerInterface
{
    public static function start(mixed $data = null): int;
    public function run(mixed $data = null): void;
}