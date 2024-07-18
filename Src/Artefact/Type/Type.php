<?php

namespace Extra\Src\Artefact\Type;

use Attribute;

interface Type {
    public static function parse(mixed $value): static|null;
    public static function selectionLabel(): string;
    public static function prepairing(): string;
}
