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
        $root = dirname(__DIR__, 4);
        $path = dirname(__DIR__) . "/Packages/$this->name";

        if (is_dir($path)) {

            if (is_dir("$path/Apis")) $this->multiCopyPHP("$path/Apis", "$root/app/Apis");
            if (is_dir("$path/Controllers")) $this->multiCopyPHP("$path/Controllers", "$root/app/Controllers");
            if (is_dir("$path/Services")) $this->multiCopyPHP("$path/Services", "$root/app/Models");
            if (is_dir("$path/Models")) $this->multiCopyPHP("$path/Models", "$root/app/Models");
            if (is_dir("$path/Repositories")) $this->multiCopyPHP("$path/Repositories", "$root/app/Repositories");
            if (is_dir("$path/Resources")) multiCopy("$path/Resources", PATH_RESOURCE . "/$this->name");
            if (is_dir("$path/static")) multiCopy("$path/static", PATH_PUBLIC . "/static/$this->name");
            Core::logMessage("Пакет {$this->name} успешно установлен!", 32);

        } else Core::logMessage("Пакет {$this->name} не существует!");
    }

    private function multiCopyPHP(string $source, string $dest, bool $over = false): void
    {
        if(!is_dir($dest)) mkdir($dest);
        if($handle = opendir($source))
        {
            while(false !== ($file = readdir($handle)))
            {
                if($file != '.' && $file != '..')
                {
                    $path = $source . '/' . $file;
                    if(is_file($path)) {
                        if(!is_file($dest . '/' . $file || $over)) if(!@copy($path, $dest . '/' . $file . '.php')) echo "('.$path.') Ошибка!!! ";
                    } elseif(is_dir($path)) {
                        if(!is_dir($dest . '/' . $file)) mkdir($dest . '/' . $file);
                        $this->multiCopyPHP($path, $dest . '/' . $file, $over);
                    }
                }
            }
            closedir($handle);
        }
    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":install      -  Инициализация пакета из репозитория.");
        Core::logLabel("End");
    }

}
