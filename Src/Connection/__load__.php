<?php

try {
    
    include 'Include/connect.php';

} catch (\Throwable $th) {
    dieConnection($th); 
}

?>