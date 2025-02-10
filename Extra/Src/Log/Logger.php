<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Log;

use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
    protected string $name;

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        dd($this->name, $level, $message, $context);
    }

    public function withName(string $name): self
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }
}
