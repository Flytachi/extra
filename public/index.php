<?php

require '../vendor/autoload.php';

\Flytachi\Extra\Extra::init();
\Flytachi\Extra\Src\Factory\Routing\Router::run(
    env('DEBUG', false)
);
