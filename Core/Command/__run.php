<?php

use Console\Core;

class __Run
{
    function __construct($value = null, $name = null)
    {
        $host = $value ?? 'localhost';
        $port = $name ?? 8000;
        $connection = @fsockopen($host, $port);

        if (is_resource($connection)) {
            Core::logMessage("Порт '$port' уже занят!");
            fclose($connection);
        } else {
            Core::logMessage("Запуск сервера 'http://" . $host . ':' . $port . "'!", 32);
            exec('php -S ' . $host . ':' . $port . ' -t public/');
        }
    }
}
