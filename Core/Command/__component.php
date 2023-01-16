<?php

use Console\Core;

class __Component
{
    private mixed $argument;
    private mixed $name;

    function __construct($value = null, $name = null)
    {
        $this->argument = $value;
        $this->name = $name;
        $this->handle();
    }

    private function handle(): void
    {
        if (!is_null($this->argument)) $this->resolution();
        else $this->help();
    }

    private function resolution(): void
    {
        if ($this->argument == "install") $this->install();
        else Core::logMessage("Команды '{$this->argument}' не существует!", 31);
    }
    
    private function install(): void
    {
        require dirname(__DIR__, 2) . '/Function/Console.php';
        $root = dirname(__DIR__, 4);
        $path = dirname(__DIR__) . "/Package/$this->name";

        if (is_dir($path)) {

            if (is_dir("$path/api")) multiCopy("$path/api", "$root/app/api");
            if (is_dir("$path/controllers")) multiCopy("$path/controllers", "$root/app/controllers");
            if (is_dir("$path/dist")) multiCopy("$path/dist", "$root/app/dist");
            if (is_dir("$path/models")) multiCopy("$path/models", "$root/app/models");
            if (is_dir("$path/repository")) multiCopy("$path/repository", "$root/app/repository");
            if (is_dir("$path/views")) multiCopy("$path/views", PATH_PUBLIC . "/views/$this->name");
            if (is_dir("$path/static")) multiCopy("$path/static", PATH_PUBLIC . "/static/$this->name");
            Core::logMessage("Пакет {$this->name} успешно установлен!", 32);

        } else Core::logMessage("Пакет {$this->name} не существует!");
    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":install      -  Инициализация пакета из репозитория.");
        Core::logLabel("End");
    }

}
