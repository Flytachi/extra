<?php

namespace Extra\Src;

abstract class Model
{
    /**
     * 
     * Model
     * 
     * @version 12.0
     */

    function __toString()
    {
        $data = [];
        foreach (array_keys(get_class_vars(get_class($this))) as $attr) $data[$attr] = $this->{$attr};
        return json_encode($data);
    }

}

?>