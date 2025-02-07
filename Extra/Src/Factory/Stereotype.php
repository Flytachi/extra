<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory;

use Flytachi\Extra\Extra;
use Psr\Log\LoggerAwareTrait;

abstract class Stereotype
{
    use LoggerAwareTrait;

    public function __construct()
    {
        self::setLogger(Extra::$logger->withName(static::class));
    }
}
