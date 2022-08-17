<?php

class __Component
{
    private $argument;
    private $name;
    private $c = "";

    function __construct($value = null, $name = null)
    {
        $this->argument = $value;
        $this->name = $name;
        $this->handle();
    }

    private function handle()
    {
        if (!is_null($this->argument)) $this->resolution();
        else $this->help();
    }

    private function resolution()
    {
        if ($this->argument == "init") $this->init();
        elseif ($this->argument == "install") $this->install();
        else echo "\033[31m"." Не такого аргумента.\n";
    }
    
    private function init()
    {
        $this->init_components();        
    }

    private function install()
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
            if (is_dir("$path/views")) multiCopy("$path/views", "$root/" . FOLDER_PUBLIC . "/views/$this->name");
            if (is_dir("$path/static")) multiCopy("$path/static", "$root/" . FOLDER_PUBLIC . "/static/$this->name");
        } else {
            echo "\033[33m". " Пакет $this->name не существует!\n";
        }
    }

    private function init_components()
    {
        $this->change_dir(dirname(__DIR__)."/$this->path");
    }

    private function change_dir(String $path, String $c_path = null)
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

    private function create_dir(String $path)
    {

        if (!file_exists($path)) mkdir($path);
    }

    private function create_file(String $path, String $code, String $sufix = "w")
    {
        if (!file_exists($path)) {
            $fp = fopen($path, $sufix);
            fwrite($fp, $code);
            fclose($fp);
        }
    }

    private function help()
    {
        echo "\033[33m"." =======> Help <======= \n";
        echo "\033[33m"."  :init    -  Инициализация фреймфорка.\n";
        echo "\033[33m"."  :install -  Инициализация пакета из репозитория.\n";
        echo "\033[33m"." =======> Help <======= \n";
    }

}

?>