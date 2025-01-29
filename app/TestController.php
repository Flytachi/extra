<?php

namespace App;

use App\Utils\Resp;
use Flytachi\Extra\Src\Factory\Http\HttpCode;
use Flytachi\Extra\Src\Factory\Http\Response\Response;
use Flytachi\Extra\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Extra\Src\Stereotype\RestController;

class TestController extends RestController
{
    #[GetMapping('test1')]
    public function test1(): void
    {
        dd();
    }

    #[GetMapping('test2')]
    public function test2(): null
    {
        return null;
    }


    #[GetMapping('test3')]
    public function test3(): Response
    {
        return new Resp(false);
    }

    #[GetMapping('test4')]
    public function test4(): Resp
    {
        $cl = new \stdClass();
        $cl->test = 'test';
        return new Resp($cl, HttpCode::BAD_REQUEST);
    }

    #[GetMapping('test5')]
    public function test5(): Resp
    {
//        try {
            throw new \TypeError("Error FileException test!", HttpCode::BAD_REQUEST->value);
//            return new Resp(false);
//        } catch (\Throwable $e) {
//            return new Resp($e->getMessage(), HttpCode::BAD_REQUEST);
//        }
//        throw new ExtraException("Error FileException test!");
//        throw new RouterException("Error FileException test!");
    }
}