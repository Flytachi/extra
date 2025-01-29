<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Error;

interface ExtraThrowable extends \Throwable
{
    public function getHeader(): array;
    public function getBody(): string;
}