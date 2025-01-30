<?php

namespace App;

use Flytachi\Extra\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Extra\Src\Stereotype\Response;
use Flytachi\Extra\Src\Stereotype\RestController;

class MainController extends RestController
{
    #[GetMapping]
    public function hello(): Response
    {
        return new Response("hello");
    }
}
