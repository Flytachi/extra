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
        if ($this->argument == "init") $this->init();
        elseif ($this->argument == "install") $this->install();
        else Core::logMessage("Команды '{$this->argument}' не существует!", 31);
    }
    
    private function init(): void
    {
        $this->init_components();        
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

    private function init_components(): void
    {
        $this->change_dir(dirname(__DIR__)."/$this->path");
    }

    private function change_dir(String $path, String $c_path = null): void
    {
        foreach (glob("$path/*") as $item) {
            if (is_dir($item)) {
                $this->c .= ($c_path and $c_path . "/" != $this->c) ? basename($c_path)."/" : "";
                $create_folder = dirname(__DIR__, 4)."/$this->c".mb_strtolower(substr(basename($item), 10, -2));
                $this->create_dir($create_folder);
                $this->change_dir($item, $create_folder);
            }else {
                $ext = ( $temp = mb_strtolower(strstr(basename($item), '_', true)) ) ? ".$temp" : "";
                $name = mb_strtolower(substr(strstr(basename($item), '_'), 2, -2));
                $Cd = explode("-", $name); 
                $newName = $Cd[0];
                for ($i=1; $i < count($Cd); $i++) $newName .= ucfirst($Cd[$i]);
                if ($c_path) $this->create_file("$c_path/$newName$ext", file_get_contents($item));
                else $this->create_file(dirname(__DIR__, 4)."/$newName$ext", file_get_contents($item));
            }
        }
    }

    private function create_dir(String $path): void
    {
        Core::logMessage("Создание директории $path.", 32);
        if (!file_exists($path)) mkdir($path);
    }

    private function create_file(String $path, String $code, String $sufix = "w"): void
    {
        if (!file_exists($path)) {
            $fp = fopen($path, $sufix);
            fwrite($fp, $code);
            fclose($fp);
        }
    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":init         -  Инициализация фреймфорка.");
        Core::logText(":install      -  Инициализация пакета из репозитория.");
        Core::logLabel("End");
    }

}
