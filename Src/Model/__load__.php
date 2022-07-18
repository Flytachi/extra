<?php

try {

    include 'Include/Trait.php';
    include 'Include/Model.php';

} catch (\Throwable $th) {
    dieConnection($th);
}

?>