<?php

namespace Extra\Src;

use TypeError;

/**
 *  Warframe collection
 * 
 *  Model - private mode
 *  
 *  All property is private, required getters and setters
 * 
 *  @version 15.0
 *  @author itachi
 *  @package Extra\Src
 */
class Model extends \stdClass
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
    function __construct(?array $data = null)
    {
        $this->reConstruct($data);
    }

    /**
     * Construct Array data to Model data
     * 
     * @param ?array $data
     * 
     * @return void
     * 
     * @throws TypeError error data property or property setter
     */
    public function reConstruct(?array $data = null): void
    {
        $properties = $this->getProperties();
        if ($data) {
            foreach ($data as $key => $value) {
                if (!array_key_exists($key, $properties)) throw new TypeError("Not key '" . $key . "' in " . get_class($this));
                $this->{$key} = $value;
                unset($properties[$key]);
            }
            foreach ($properties as $property => $type) {
                if (gettype($this->{$property})) unset($properties[$property]);
            }
        }
    }

    private function getProperties(): array
    {
        $reflection = new \ReflectionClass($this);
        $property = [];
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflectionProperty)
            $property[$reflectionProperty->getName()] = (string) $reflectionProperty->getType();
        return $property;
    }
}
