<?php

namespace Extra\Src\Annotation\Postman;

interface Postman
{
    public function prepare(array &$arrayData): void;
}