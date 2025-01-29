<?php

require_once '../vendor/autoload.php';

\Flytachi\Extra\Extra::init();
\Flytachi\Extra\Src\Factory\Routing\Router::run(
    (bool) env('DEBUG', false)
);
