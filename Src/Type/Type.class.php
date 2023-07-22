<?php

namespace Extra\Src\Type;

use Attribute;

#[Attribute] interface Type {
    public static function sqlEncode(mixed $value): string;
    public static function sqlDecode(): string|null;
}
