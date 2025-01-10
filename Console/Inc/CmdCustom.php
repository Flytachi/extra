<?php

namespace Extra\Console\Inc;

abstract class CmdCustom extends Printer implements CmdCustomInterface
{
    protected array $args;

    public final function __construct(array $args)
    {
        $this->args = $args;
        try {
            $this->init();
            $this->handle();
        } catch (\Throwable $exception) {
            self::printTitle(static::class, 31);
            self::printError($exception);
        }
    }

    public final static function script(array $args): void
    {
        new static($args);
    }

    protected function init(): void
    {}

}