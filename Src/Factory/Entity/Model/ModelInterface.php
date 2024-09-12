<?php

namespace Extra\Src\Factory\Entity\Model;

interface ModelInterface
{
    function __toString(): string;
    public function __construct();
    public static function selection(): array;
}
