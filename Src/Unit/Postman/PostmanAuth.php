<?php

namespace Extra\Src\Unit\Postman;

use Extra\Src\Unit\Postman\Auth\PostmanAuthInterface;

class PostmanAuth implements Postman
{
    public function prepare(array &$arrayData): void {
        $arrayData[] = [];
    }

    public static function morph(?PostmanAuthInterface $auth = null): array
    {
        if (!is_null($auth)) return $auth->meta();
        else return [];
    }
}