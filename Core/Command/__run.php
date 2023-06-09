<?php

use Console\Core;

class __Run
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

    public function handle(): void
    {
        if (!is_null($this->argument)) $this->resolution();
        else $this->help();
    }

    private function resolution(): void
    {
        if ($this->argument == "serve") $this->serve();
        elseif ($this->argument == "script") $this->script();
        else Core::logMessage("Команды '{$this->argument}' не существует!", 31);
    }

    private function serve(): void
    {
        if ($this->name) {
            $data = explode(':', $this->name);
            $host = array_key_exists(0, $data) ? $data[0] : 'localhost';
            $port = array_key_exists(1, $data) ? $data[1] : 8000;
        } else {
            $host = 'localhost';
            $port = 8000;
        }

        // Start
        $connection = @fsockopen($host, $port);
        if (is_resource($connection)) {
            Core::logMessage("Порт '$port' уже занят!");
            fclose($connection);
        } else {
            Core::logMessage("Запуск сервера 'http://" . $host . ':' . $port . "'!", 32);
            exec('php -S ' . $host . ':' . $port . ' -t public/');
        }
    }

    private function script(): void
    {
        require PATH_APP . '/constants.php';
        if ($this->name) {
            if (array_key_exists($this->name, SCRIPTS)) {
                $script = SCRIPTS[$this->name];
                if (is_file($script)) {
                    if (is_executable($script)) echo shell_exec($script);
                    else Core::logMessage("Указанный скрипт не является исполняемым.");
                } else Core::logMessage("Указанный скрипт не найден.");
            } else Core::logMessage("Указанное имя скрипта не найдено.");
        } else Core::logMessage("Не указано имя скрипта.");
    }

    public function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":serve        -  Запуск сервера (можно указать хост и порт, по умолчанию \"localhost:8000\").");
        Core::logText(":script       -  Запуск заготовленного скрипта, указанного в \"constants.php\" (укажите имя скрипта).");
        Core::logLabel("End");
    }

}
