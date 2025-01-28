<?php

declare(strict_types=1);

namespace Flytachi\Extra\Stereotype;

use Flytachi\Extra\Extra;
use Monolog\Logger;

abstract class StereotypeBase
{
    protected Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger(static::class);
        $this->logger->pushHandler(Extra::getLoggerStreamHandler());
    }
}
