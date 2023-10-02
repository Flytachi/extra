<?php

namespace Extra\Console\Inc;
interface CmdInterface
{
    public function handle(): void;
    public static function help(): void;
}