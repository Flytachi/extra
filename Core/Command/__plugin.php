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
                Core::logText(PATH_PLUGIN . $this->name);
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