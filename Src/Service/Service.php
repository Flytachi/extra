<?php

namespace Extra\Src\Service;

use Extra\Src\Log\Log;
use ReflectionClass;

/**
 * Class Service
 *
 * Service` is an abstract base class that implements logic and also provides a consistent way to handle linked repositories.
 * It ensures that the repositories are instantiated when the service class is constructed.
 * All service classes in the application are expected to inherit from this base class.
 *
 * The class provides a constructor that iterates through all the properties of the derived class.
 * If the property type is a subclass of `Repository`, an instance of that subclass is created and assigned to the property.
 *
 * @version 2.0
 * @author Flytachi
 */
abstract class Service
{
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
    }
}