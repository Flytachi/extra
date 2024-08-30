<?php

namespace Extra\Src\Entity\Model;

use Attribute;
use Extra\Src\Entity\EntityError;
use Extra\Src\HttpCode;
use TypeError;

/**
 * Class ModelBase
 *
 * `ModelBase` is a base class for models in the application. It extends the stdClass and provides the base functionality
 * for transforming and presenting models.
 *
 * The methods provided by `ModelBase` include:
 *
 * - `__toString(): string`: Converts model data to JSON format.
 * - `__construct(): void`: Default constructor of the model.
 * - `arrayToObject(?array $data = null): void`: A static function that creates a model instance from an associative array of data.
 *
 * @version 16.1
 * @author Flytachi
 */
#[Attribute]
class ModelBase extends \stdClass
{
    function __toString(): string
    {
        $data = [];
        foreach (get_object_vars($this) as $key => $value) $data[$key] = $value;
        return json_encode($data);
    }

    /**
     * Model constructor
     */
    function __construct() {}

    public static function selection(): array
    {
        return [];
    }

}
