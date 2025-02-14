<?php

require '../vendor/autoload.php';

Flytachi\Kernel\Extra::init();
Flytachi\Kernel\Src\Factory\Routing\Router::run(
    env('DEBUG', false)
);
