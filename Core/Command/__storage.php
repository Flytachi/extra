<?php

class __Storage
{
    private $argument;
    private $name;
    private String $path = "storage";

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
        else echo "\033[31m"." Не такого аргумента.\n";
    }

    private function init()
    {
        require_once dirname(__DIR__, 3) . '/tools/variables.php';
        if (exec("mkdir $this->path && echo 1")) echo "\033[32m"." => Директория $this->path создана.\n";
        // storage
        if ( isset($storage) ) {
            foreach ($storage as $folder) {
                if (exec("mkdir $this->path/$folder && echo 1")) echo "\033[32m"." => Директория $this->path/$folder создана.\n";
            }
        }

        if (exec("chmod -R 777 $this->path && echo 1")) echo "\033[32m"." Права на запись установлены.\n";
        return 1;
    }

    private function help()
    {
        echo "\033[33m"." =======> Help <======= \n";
        echo "\033[33m"."  :init   -  создать папку для хранений персональный даных (файлы).\n";
        echo "\033[33m"." =======> Help <======= \n";
    }

}

?>