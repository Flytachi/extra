<?php

namespace Extra\Src\Entity\Model;

interface ModelInterface
{
    function __toString(): string;
    public function __construct();
    public static function arrayToObject(?array $data = null): void;
    public static function selection(): array;
}
