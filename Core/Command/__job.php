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
            dd($e->getMessage());
            Core::logMessage("Ошибка в скрипте.", 31);
        }
    }

    private function start(): void
    {
        if ($this->name) {

            $processId = exec(sprintf(
                'php -q ../box job:run %s > %s 2>&1 & echo $!',
                str_replace('\\', '\\\\', static::class),
                "/dev/null"
            ));
            Core::logMessage("Задача {$this->name} запущена!", 32);
            Core::logMessage("PID: " . $processId, 32);

        } else Core::logMessage("Укажите имя задачи!");
    }

    private function run(): void
    {
        global $argv;
        if ($this->name) {
            if (array_key_exists(3, $argv) && $argv[3]) {
                $filePath = PATH_CACHE . '/' . $argv[3];
                if (is_file($filePath)) {
                    $data = unserialize(file_get_contents($filePath));
                    unlink($filePath);
                }
            } else $data = null;
            ($this->name)::start($data);
        }
        else Core::logMessage("Укажите имя задачи!");
    }

    private function visorRun(): void
    {
        global $argv;
        if (array_key_exists(3, $argv)) {
            $filePath = PATH_CACHE . '/' . $argv[3];
            $data = unserialize(file_get_contents($filePath));
            unlink($filePath);
        } else $data = null;
        if ($this->name) ($this->name)::visorStart($data);
        else Core::logMessage("Укажите имя задачи!");
    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":run          -  Запустить сокет сервер.");
        Core::logText(":start        -  Запустить сокет сервер в фоновом процессе.");
//        Core::logText(":status       -  Статус сокетов.");
        Core::logLabel("End");
    }

}