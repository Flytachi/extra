<?php

class __Component
{
    private $argument;
    private $name;
    private $c = "";
    private String $path = "Components";

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
        elseif ($this->argument == "apache") $this->apache();
        // elseif ($this->argument == "nginx") $this->nginx();
        else echo "\033[31m"." Не такого аргумента.\n";
    }
    
    private function init()
    {
        $this->init_components();        
    }

    private function apache()
    {
        $file = dirname(__DIR__) . '/Template/Server/apache';
        $errors = "";
        if (file_exists(CFG_PATH_CLOSE)) {
            $ini = cfgGet();
            $dir = dirname(__DIR__, 4) . '/';
            $template = str_replace("__PORT__", $ini['APACHE']['SERVER_PORT'], file_get_contents($file));
            $template = str_replace("__ADMIN__", $ini['APACHE']['SERVER_ADMIN'], $template);
            $template = str_replace("__ALIAS__", $ini['APACHE']['SERVER_ALIAS'], $template);
            $template = str_replace("__NAME__", $ini['APACHE']['SERVER_NAME'], $template);
            $template = str_replace("__ROOT__", $dir . APP_PUBLIC . '/', $template);
            $template = str_replace("__DIR__", $dir, $template);
            $fp = fopen($dir . APP_FOLDER . '/apache.conf', "w");
            fwrite($fp, $template);
            fclose($fp);
            echo "\033[32m". " Apache конфигурация сгенерирована успешно!\n";
        } else echo "Configuration file not found.\n";
    }

    // private function nginx()
    // {
    //     $file = dirname(__DIR__) . '/Template/Server/nginx';
    //     $errors = "";
    //     if (file_exists(cfgPathClose)) {
    //         $ini = cfgGet();
    //         $template = str_replace("__PORT__", $ini['PORT'], file_get_contents($file));
    //         $template = str_replace("__ROOT__", dirname(__DIR__, 3), $template);
    //         $template = str_replace("__HOSTS__", implode(' ', $ini['HOSTS']), $template);
    //         foreach (glob(dirname(__DIR__)."/$this->path/__FOLDER__ERROR__/*") as $value) {
    //             $ext = ( $temp = mb_strtolower(strstr(basename($value), '_', true)) ) ? ".$temp" : "";
    //             $name = mb_strtolower(substr(strstr(basename($value), '_'), 2, -2));
    //             $errors .= "\n\terror_page $name /error/$name$ext;";
    //         }
    //         $template = str_replace("__ERRORS__", $errors, $template);

    //         $fp = fopen(dirname(__DIR__, 3)."/tools/nginx.conf", "w");
    //         fwrite($fp, $template);
    //         fclose($fp);

    //     } else echo "Configuration file not found.\n";

        
        
    // }

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
        echo "\033[33m"."  :init   -  Инициализация фреймфорка.\n";
        echo "\033[33m"."  :apache -  Создать конфигурационный файл (Apache).\n";
        echo "\033[33m"."  :nginx  -  Создать конфигурационный файл (Nginx).\n";
        echo "\033[33m"." =======> Help <======= \n";
    }

}

?>