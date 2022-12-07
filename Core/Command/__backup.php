<?php

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
            else echo "\033[31m"." Нет такого аргумента.\n";
        } catch (Error) {
            echo "\033[31m"." Ошибка в скрипте.\n";
        }
    }

    private function init(): void
    {
        if (!is_dir($this->path)) {
            if (mkdir($this->path, 0764, true)) echo "\033[32m"." Директория для резервного копирования создана.\n";
        }else echo "\033[32m"." Директория для резервного копирования уже существует.\n";
    }

    private function remove(): void
    {
        if (is_dir($this->path)) {
            if ($this->delTree($this->path)) echo "\033[32m"." Директория для резервного удаленна.\n";
        }else echo "\033[32m"." Директория для резервного копирования не существует.\n";
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
            foreach ($scanned_files as $file) {
                print_r(pathinfo($file, PATHINFO_FILENAME)."\n");
            }
        }else echo "\033[33m"." Директория для резервного копирования не существует.\n";
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
            exec("mysqldump -u'$user' -p'$pass' -h'$host' --protocol=TCP -P'$port' $name > $this->path/$file_name.$this->file_format");
            echo "\033[32m"." Дамп успешно создан.\n";
        }else echo "\033[33m"." Директория для резервного копирования не существует.\n";
    }

    private function delete(): void
    {
        if ($this->is_dir()) {
            if ($this->name) {
                unlink("$this->path/$this->name.$this->file_format");
                echo "\033[32m"." Дамп успешно удалён.\n";
            }else echo "\033[33m"." Введите название удаляемого дампа.\n";
        }else echo "\033[33m"." Директория для резервного копирования не существует.\n";
    }

    private function migrate(): void
    {
        if ($this->is_dir()) {
            if ($this->name) {
                $ini = cfgGet();
                exec("mysql -u " . $ini['DATABASE']['USER'] . " -p" . $ini['DATABASE']['PASS'] . " " . $ini['DATABASE']['NAME'] . " < $this->path/$this->name.$this->file_format");
                echo "\033[32m"." Миграция дампа прошла успешно.\n";
            }else echo "\033[33m"." Введите название файла дампа.\n";
        }else echo "\033[33m"." Директория для резервного копирования не существует.\n";
    }

    private function help(): void
    {
        echo "\033[33m"." =======> Help <======= \n";
        echo "\033[33m"."  :init     -  Создать директорию резервного копирования.\n";
        echo "\033[33m"."  :remove   -  Удалить директорию резервного копирования.\n";
        echo "\033[33m"."  :show     -  Просмотр резервных копий.\n";
        echo "\033[33m"."  :create   -  Создать резервную копию.\n";
        echo "\033[33m"."  :delete   -  Удалить резервную копию (указать резервную копию).\n";
        echo "\033[33m"."  :migrate  -  Востановить резервную копию (указать резервную копию).\n";
        echo "\033[33m"." =======> Help <======= \n";
    }

}
