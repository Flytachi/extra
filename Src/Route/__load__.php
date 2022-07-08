<?php

try {

    include 'Include/Route.php';

} catch (\Throwable $th) {
    dieConnection($th);
}

?>