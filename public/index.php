<?php

require '../vendor/autoload.php';

Flytachi\Kernel\Extra::init();
Flytachi\Kernel\Src\Http\Router::run(
    env('DEBUG', false)
);
