<?php

declare(strict_types=1);

namespace Flytachi\Extra\Console\Inc;

interface CmdInterface
{
    public function handle(): void;
    public static function help(): void;
}
