<?php

class __Serve // !*
{

    function __construct($value = null, $name = null)
    {
        $cfg = str_replace("\n", "", file_get_contents(dirname(__DIR__, 2)."/.cfg") );
        $ini = json_decode(zlib_decode(hex2bin($cfg)), true);
        echo "\033[32m"." Сокет сервер успешно запущен.\n";
        
        require 'socket.php';
    }

}

?>
