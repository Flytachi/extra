<?php

try {

    include 'Include/Interface.php';
    include 'Include/Trait.php';
    include 'Include/Controller.php';

} catch (\Throwable $th) {
    dieConnection($th);
}

?>