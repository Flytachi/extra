<?php

namespace App;

use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Stereotype\Response;
use Flytachi\Kernel\Src\Stereotype\RestController;

class MainController extends RestController
{
    #[GetMapping]
    public function hello(): Response
    {
        return new Response("hello");
    }
}
