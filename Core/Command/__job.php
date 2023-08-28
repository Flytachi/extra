<?php

use Console\Core;

class __job
{
    private mixed $argument;
    private mixed $name;
    private string $PIDStorage = 'pid';

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
        try {
            if ($this->argument == "run") $this->run();
            elseif ($this->argument == "start") $this->start();
//            elseif ($this->argument == "status") $this->status();
            else Core::logMessage("Команды '{$this->argument}' не существует!", 31);
        } catch (Error $e) {
            Core::logMessage("Ошибка в скрипте.", 31);
        }
    }

    private function start(): void
    {
        if ($this->name) {

            $processId = exec(sprintf(
                '%s > %s 2>&1 & echo $!',
                "php -q box job:run " . str_replace('\\', '\\\\', $this->name),
                "/dev/null"
            ));
            Core::logMessage("Задача {$this->name} запущена!", 32);
            Core::logMessage("PID: " . $processId, 32);

        } else Core::logMessage("Укажите имя задачи!");
    }

    private function run(): void
    {
        if ($this->name) ($this->name)::start();
        else Core::logMessage("Укажите имя задачи!");
    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":run          -  Запустить сокет сервер.");
        Core::logText(":start        -  Запустить сокет сервер в фоновом процессе.");
        Core::logText(":status       -  Статус сокетов.");
        Core::logLabel("End");
    }

}
