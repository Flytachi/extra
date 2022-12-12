<?php

use Console\Core;

class __Backup
{
    private mixed $argument;
    private mixed $name;
    private String $path;
    private String $file_format = "sql";

    function __construct($value = null, $name = null)
    {
        $this->path = dirname(__DIR__, 4)."/backup";
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
            elseif ($this->argument == "remove") $this->remove();
            elseif ($this->argument == "show") $this->show();
            elseif ($this->argument == "create") $this->create();
            elseif ($this->argument == "delete") $this->delete();
            elseif ($this->argument == "migrate") $this->migrate();
            else Core::logMessage("Нет такого аргумента.", 31);
        } catch (Error) {
            Core::logMessage("Ошибка в скрипте.", 31);
        }
    }

    private function init(): void
    {
        if (!is_dir($this->path)) {
            if (mkdir($this->path, 0764, true)) Core::logMessage("Директория для резервного копирования создана.", 32);
        }else Core::logMessage("Директория для резервного копирования уже существует.");
    }

    private function remove(): void
    {
        if (is_dir($this->path)) {
            if ($this->delTree($this->path)) Core::logMessage("Директория для резервного удаленна.", 32);
        }else Core::logMessage("Директория для резервного копирования не существует.");
    }

    private function delTree($dir): bool
    {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function is_dir(): bool
    {
        if (is_dir($this->path)) return true;
        else return false;
    }

    private function show(): void
    {
        if ($this->is_dir()) {
            $scanned_files = array_diff(scandir($this->path), array('..', '.'));
            Core::logLabel("Дампы");
            foreach ($scanned_files as $file) {
                Core::logText(pathinfo($file, PATHINFO_FILENAME));
            }
            Core::logLabel("Дампы");
        }else Core::logMessage("Директория для резервного копирования не существует.");
    }

    private function create(): void
    {
        if ($this->is_dir()) {
            $ini = cfgGet();
            $file_name = $this->name ?? date("Y-m-d_H-i-s");
            $user = $ini['DATABASE']['USER'];
            $pass = $ini['DATABASE']['PASS'];
            $host = $ini['DATABASE']['HOST'];
            $port = $ini['DATABASE']['PORT'];
            $name = $ini['DATABASE']['NAME'];
            $fileName = $this->path . '/' . $file_name . '.' . $this->file_format;
            exec("mysqldump -u'$user' -p'$pass' -h'$host' --protocol=TCP -P'$port' $name > $fileName");
            Core::logMessage("Дамп успешно создан.", 32);
            Core::logMessage("* backup: '" . basename($fileName, '.sql') . "'", 32);
        }else Core::logMessage("Директория для резервного копирования не существует.");
    }

    private function delete(): void
    {
        if ($this->is_dir()) {
            if ($this->name) {
                $fileName = "$this->path/$this->name.$this->file_format";
                if (file_exists($fileName)) {
                    unlink($fileName);
                    Core::logMessage("Дамп успешно удалён.", 32);
                } else Core::logMessage("Дамп не найден.");
            } else Core::logMessage("Введите название удаляемого дампа.");
        } else Core::logMessage("Директория для резервного копирования не существует.");
    }

    private function migrate(): void
    {
        if ($this->is_dir()) {
            if ($this->name) {
                $fileName = "$this->path/$this->name.$this->file_format";
                if (file_exists($fileName)) {
                    
                    $ini = cfgGet();
                    exec("mysql -u " . $ini['DATABASE']['USER'] . " -p" . $ini['DATABASE']['PASS'] . " " . $ini['DATABASE']['NAME'] . " < $fileName");
                    Core::logMessage("Миграция дампа прошла успешно.", 32);

                } else Core::logMessage("Дамп не найден.");
            }else Core::logMessage("Введите название файла дампа.");
        }else Core::logMessage("Директория для резервного копирования не существует.");
    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":init         -  Создать директорию резервного копирования.");
        Core::logText(":create       -  Создать резервную копию (можно указать имя копии).");
        Core::logText(":show         -  Просмотр резервных копий.");
        Core::logText(":migrate      -  Востановить резервную копию (нужно указать резервную копию).");
        Core::logText(":delete       -  Удалить резервную копию (нужно указать резервную копию).");
        Core::logText(":remove       -  Удалить директорию резервного копирования.");
        Core::logLabel("End");
    }

}
