<?php

namespace Extra\Src;

use TypeError;

/**
 *  Warframe collection
 * 
 *  ModelEasy - public mode
 *  
 *  All property is public
 * 
 * @version 13.0
 * @author itachi
 */
abstract class ModelEasy
{
    function __toString(): string
    {
        $data = [];
        foreach (array_keys(get_class_vars(get_class($this))) as $attr) $data[$attr] = $this->{$attr};
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
     * @throws TypeError error data property
     */
    public function reConstruct(?array $data = null): void
    {
        if ($data) {
            foreach ($data as $key => $value) {
                if (!property_exists($this, $key)) throw new TypeError("Not key '" . $key . "' in " . get_class($this));
                else $this->{$key} = $value;
            }
        }
    }
}
