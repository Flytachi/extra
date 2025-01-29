<?php

declare(strict_types=1);

namespace Flytachi\Extra\Factory\Error;

use Dotenv\Exception\ExceptionInterface;

interface ExtraThrowable extends \Throwable
{
    public function getHeader(): array;
    public function getBody(): string;
}