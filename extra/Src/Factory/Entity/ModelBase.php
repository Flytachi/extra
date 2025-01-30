<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Entity;

class ModelBase extends \stdClass implements ModelInterface
{
    final public function __toString(): string
    {
        $data = [];
        foreach (get_object_vars($this) as $key => $value) {
            $data[$key] = $value;
        }
        return json_encode($data);
    }

    /**
     * Model constructor
     */
    final public function __construct()
    {
    }

    public static function selection(): array
    {
        return [];
    }
}
