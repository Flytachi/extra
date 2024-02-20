<?php

namespace Extra\Src\Unit\Postman\Auth;

use Extra\Src\Unit\Postman\Postman;

interface PostmanAuthInterface extends Postman
{
    public function meta(): array;
}