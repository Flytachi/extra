<?php

try {

    include 'Include/Interface.php';
    include 'Include/Trait.php';
    include 'Include/Credo.php';
    
} catch (\Throwable $th) {
    dieConnection($th); 
}

?>