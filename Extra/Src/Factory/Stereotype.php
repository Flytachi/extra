<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory;

use Flytachi\Extra\Extra;
use Monolog\Logger;

abstract class Stereotype
{
    protected Logger $logger;

    public function __construct()
    {
        $this->logger = Extra::$logger->withName(static::class);
    }
}
