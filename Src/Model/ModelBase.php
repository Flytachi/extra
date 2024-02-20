<?php

namespace Extra\Src\Model;

use Attribute;
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
 * @version 16.0
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

    /**
     * Convert an array to an object.
     *
     * @param array|null $data The array containing the data to convert.
     * @throws TypeError If a key in the array does not exist as a property in the instance.
     */
    public static function arrayToObject(?array $data = null): void
    {
        if ($data) {
            try {
                $instance = new static();
                $properties = get_object_vars($instance); // retrieve properties of the instance

                foreach ($data as $key => $value) {
                    if (!array_key_exists($key, $properties))
                        throw new TypeError("Not key '" . $key . "' in " . get_class($instance));
                    $instance->{$key} = $value;
                    unset($properties[$key]);
                }
                foreach ($properties as $property => $type) {
                    if (isset($instance->{$property})) unset($properties[$property]);
                }
            } catch (\Throwable $exception) {
                ModelError::throw(HttpCode::INTERNAL_SERVER_ERROR, $exception->getMessage());
            }
        }
    }

}
