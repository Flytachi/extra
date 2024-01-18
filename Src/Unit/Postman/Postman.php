<?php

namespace Extra\Src\Unit\Postman;

interface Postman
{
    public function prepare(array &$arrayData): void;
}