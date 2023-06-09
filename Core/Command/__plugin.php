<?php

use Console\Core;

class __Plugin
{
    private mixed $argument;
    private mixed $name;

    function __construct($value = null, $name = null)
    {
        Warframe::coreLoader();
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
        try {
            if ($this->argument == "init") $this->init();
            elseif ($this->argument == "create") $this->create();
            else Core::logMessage("Команды '{$this->argument}' не существует!", 31);
        } catch (Error $e) {
            Core::logMessage("Ошибка в скрипте.", 31);
        }
    }

    private function init(): void
    {
        if (!is_dir(PATH_PLUGIN)) {
            if (mkdir(PATH_PLUGIN)) Core::logMessage("Директория для плагинов создана.", 32);
        }else Core::logMessage("Директория для плагинов уже существует.");
    }

    private function create(): void
    {
        if (isset($this->name)) {
            if (is_dir(PATH_PLUGIN)) {
                $pluginDir = PATH_PLUGIN . '/Frame.' . $this->name;
                if (!is_dir($pluginDir)) {

                    mkdir($pluginDir);
                    mkdir($pluginDir . '/api');
                    mkdir($pluginDir . '/controllers');
                    mkdir($pluginDir . '/models');
                    mkdir($pluginDir . '/repository');
                    mkdir($pluginDir . '/sockets');
                    $file = dirname(__DIR__) . "/Template/pluginFrame";
                    $template = str_replace("__NAME__", $this->name, file_get_contents($file));

                    $fp = fopen($pluginDir . '/__frame__.php', "x");
                    fwrite($fp, $template);
                    fclose($fp);

                    Core::logMessage("Плагин '". 'Frame.' . $this->name ."' создан.", 32);
                } else Core::logMessage("Плагин уже существует.");
            }
            else Core::logMessage("Директория для плагинов не существует.");
        }
        else Core::logMessage("Укажите корректное имя шаблона.");
    }

    public function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":create       -  Создать конструктор (укажите имя плагина).");
        Core::logText(":npm          -  Установить компоненты \"npm\".");
        Core::logLabel("End");
    }

}

?>