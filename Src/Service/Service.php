<?php

namespace Extra\Src\Service;

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
        $reflect = new ReflectionClass($this);
        foreach ($reflect->getProperties() as $property) {
            if (strrpos($property->getType(), 'Repository'))
                $this->{$property->getName()} = new ($property->getType()->getName());
        }
    }
}