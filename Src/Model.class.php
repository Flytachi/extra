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
 *  @version 13.0
 *  @author itachi
 *  @package Extra\Src
 */
abstract class Model
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
     * @throws TypeError error data property or property setter
     */
    public function reConstruct(?array $data = null): void
    {
        if ($data) {
            foreach ($data as $key => $value) {
                $func = 'set' . str_replace(" ", "", ucwords(str_replace("_", " ", $key)));
                if (!property_exists($this, $key)) throw new TypeError("Not key '" . $key . "' in " . get_class($this));
                if (!method_exists($this, $func)) throw new TypeError("Not method '" . $func . "' in " . get_class($this));
                else $this->{$func}($value);
            }
        }
    }
}
