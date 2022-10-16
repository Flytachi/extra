<?php

namespace Extra\Src;

use TypeError;

trait ModelIterator
{
    public function __construct(array $data = null)
    {
        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists(__CLASS__, $key)) {
                    $this->{$key} =  $value;
                } else {
                    throw new TypeError("Not key '" . $key . "' in " . __CLASS__);
                }
            }
        }
    }

    public function setNewObject(array $data = null): void
    {
        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists(__CLASS__, $key)) {
                    if ($key) $this->{$key} = $value;
                } else {
                    throw new TypeError("Not key '" . $key . "' in " . __CLASS__);
                }
            }
        }
    }
}
