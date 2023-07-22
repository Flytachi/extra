<?php

namespace Extra\Src\Type;

use Attribute;

#[Attribute] class JsonType implements Type {
    public static function parse(array|string|null|self $value): array
    {
        if (is_array($value)) return $value;
        if (is_null($value)) return [];
        else return json_decode($value, 1);
    }

    public static function sqlEncode(mixed $value): string
    {
        if (is_array($value)) return json_encode($value);
        elseif (is_string($value)) return $value;
        else throw new \TypeError(self::class . " data type error.");
    }

    public static function sqlDecode(): string|null
    {
        return null;
    }
}