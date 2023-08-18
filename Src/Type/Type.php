<?php

namespace Extra\Src\Type;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
interface Type {
    public static function write(mixed $value): string;
    public static function read(): string;
    public static function readLabel(): string;
}
