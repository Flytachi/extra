<?php

namespace Extra\Src\Annotation\Postman\Auth;

use Extra\Src\Annotation\Postman\Postman;

interface PostmanAuthInterface extends Postman
{
    public function meta(): array;
}