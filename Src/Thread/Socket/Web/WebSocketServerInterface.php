<?php

namespace Extra\Src\Thread\Socket\Web;

interface WebSocketServerInterface
{
    public static function start(mixed $data = null): int;
    public function run(mixed $data = null): void;
}