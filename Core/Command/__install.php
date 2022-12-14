<?php

use Console\Core;

class __Install
{
    private mixed $argument;
    private mixed $name;

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
        try {
            if ($this->argument == "git") $this->git();
            elseif ($this->argument == "npm") $this->npm();
            else Core::logMessage("Команды '{$this->argument}' не существует!", 31);
        } catch (Error $e) {
            Core::logMessage("Ошибка в скрипте.", 31);
        }
    }

    private function npm()
    {
        Core::logLabel("В разработке!");
        // require PATH_APP . '/constants.php';
        // Core::logTitle("Установка npm библиотек", 32);
        // echo exec("npm install");
        // Core::logTitle("=======================", 32);
    }

    private function git()
    {
        require PATH_APP . '/constants.php';
        Core::logTitle("Установка git библиотек", 32);
        foreach (GIT_LIBS as $link => $folder) {

            if (is_dir(PATH_LIB . '/' . $folder))
                Core::logLabel("$folder => уже существует!");
            else {
                Core::logLabel("$folder => $link", 32);
                echo exec("git clone $link " . PATH_LIB . '/ ' . $folder);
                Core::logLabel("$folder END", 32);
            }
        }
        Core::logTitle("=======================", 32);
    }

    public function help()
    {
        Core::logLabel("Help");
        Core::logText(":git          -  Установить компоненты \"git\".");
        Core::logText(":npm          -  Установить компоненты \"npm\".");
        Core::logLabel("End");
    }

}

?>