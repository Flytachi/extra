<?php

class __Run
{
    function __construct($value = null, $name = null)
    {
        $host = $value ?? 'localhost';
        $port = $name ?? 8000;
        $connection = @fsockopen($host, $port);

        if (is_resource($connection)) {
            echo "\033[33m". " Порт '$port' уже занят!\n";
            fclose($connection);
        } else {
            echo "\033[32m"." Запуск сервера 'http://" . $host . ':' . $port . "'!\n";
            echo "\033[0m";
            exec('php -S ' . $host . ':' . $port . ' -t public/');
        }
        
    }
}
