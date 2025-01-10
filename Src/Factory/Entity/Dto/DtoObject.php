<?php

namespace Extra\Src\Factory\Entity\Dto;

use Extra\Src\Factory\Entity\Entity;


/**
 * Class DtoObject
 *
 * This class is an abstract base class for DTO (Data Transfer Object) objects.
 * It extends the Entity class and provides additional functionality for preparing the data.
 *
 * @version 1.0
 * @author Flytachi
 */
abstract class DtoObject extends Entity
{
    protected function preparing(): void
    {}

    public final function __construct(array $data)
    {
        parent::__construct($data);
        $this->preparing();
    }
}