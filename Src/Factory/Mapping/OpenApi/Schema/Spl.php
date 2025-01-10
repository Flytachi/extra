<?php

namespace Extra\Src\Factory\Mapping\OpenApi\Schema;

interface Spl
{
    public function modify(array &$path): void;
}