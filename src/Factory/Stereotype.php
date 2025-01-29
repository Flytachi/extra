<?php

declare(strict_types=1);

namespace Flytachi\Extra\Factory;

use Flytachi\Extra\Extra;
use Monolog\Logger;

abstract class Stereotype
{
    protected Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger(static::class);
        $this->logger->pushHandler(Extra::$loggerStreamHandler);
    }
}
