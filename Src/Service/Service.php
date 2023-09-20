<?php

namespace Extra\Src\Service;

use Extra\Src\Log\Log;
use ReflectionClass;

abstract class Service
{
    /** @var array $storage betta test */
    protected array $storage = [];

    /**
     * Constructor
     *
     * Initializes the specified Repositories
     *
     * @return void
     */
    public function __construct()
    {
        Log::trace('Service construct: ' . static::class);
        $reflect = new ReflectionClass($this);
        foreach ($reflect->getProperties() as $property) {
            if (strrpos($property->getType(), 'Repository'))
                $this->{$property->getName()} = new ($property->getType()->getName());
        }
    }
}