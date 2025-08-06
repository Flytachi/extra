<?php

require '../bootstrap.php';

\Flytachi\Kernel\Src\Actuator::use(
//    new \Flytachi\Kernel\Src\Health\Health(), // health check endpoints
    new \Flytachi\Kernel\Src\Http\Router()
);
