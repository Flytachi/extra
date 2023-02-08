<?php

use Console\Core;

class __Make
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
        try {
            if (in_array($this->argument, ['auto']))                    $this->mAuto();
            elseif (in_array($this->argument, ['api']))                 $this->mApi();
            elseif (in_array($this->argument, ['controller']))          $this->mController();
            elseif (in_array($this->argument, ['model']))               $this->mModel();
            elseif (in_array($this->argument, ['socket']))              $this->mSocket();
            elseif (in_array($this->argument, ['repo', 'repository']))  $this->mRepository();
            else Core::logMessage("Шаблона '{$this->argument}' не существует!");
        } catch (Error) {
           Core::logMessage("Ошибка в скрипте.", 31);
        }
        
    }

    private function mAuto(): void
    {
        if (strrpos($this->name, 'Api')) {
            $this->argument = 'api';
            $this->mApi();
        } elseif (strrpos($this->name, 'Controller')) {
            $this->argument = 'controller';
            $this->mController();
        } elseif (strrpos($this->name, 'Model')) {
            $this->argument = 'model';
            $this->mModel();
        } elseif (strrpos($this->name, 'Socket')) {
            $this->argument = 'socket';
            $this->mSocket();
        } elseif (strrpos($this->name, 'Repository')) {
            $this->argument = 'repository';
            $this->mRepository();
        }
    }

    private function mApi(): void
    {
        if ($this->name && !strrpos($this->name, 'Api')) {
            Core::logMessage("Укажите корректное имя шаблона.");
        }elseif ($this->name) {
            $file = dirname(__DIR__) . "/Template/api";
            $template = str_replace("_ApiIndex_", $this->UC_word($this->name), file_get_contents($file));
            $this->create_file($this->UC_word($this->name), basename(dirname(__DIR__, 3)) . '/api', $template);
        } else Core::logMessage("Укажите имя для шаблона.");
    }

    private function mController(): void
    {
        if ($this->name && !strrpos($this->name, 'Controller')) {
            Core::logMessage("Укажите корректное имя шаблона.");
        }elseif ($this->name) {
            $file = dirname(__DIR__) . "/Template/controller";
            $template = str_replace("_ControllerIndex_", $this->UC_word($this->name), file_get_contents($file));
            $template = str_replace("_RepositoryIndex_", str_replace('Controller', 'Repository', $this->UC_word($this->name)), $template);
            $this->create_file($this->UC_word($this->name), basename(dirname(__DIR__, 3)) . '/controllers', $template);
        } else Core::logMessage("Укажите имя для шаблона.");
    }

    private function mModel(): void
    {
        if ($this->name && !strrpos($this->name, 'Model')) {
            Core::logMessage("Укажите корректное имя шаблона.");
        }elseif ($this->name) {
            $file = dirname(__DIR__) . "/Template/model";
            $template = str_replace("_ModelIndex_", $this->UC_word($this->name), file_get_contents($file));
            $this->create_file($this->UC_word($this->name), basename(dirname(__DIR__, 3)) . '/models', $template);
        } else Core::logMessage("Укажите имя для шаблона.");
    }

    private function mSocket(): void
    {
        if ($this->name && !strrpos($this->name, 'Socket')) {
            Core::logMessage("Укажите корректное имя шаблона.");
        }elseif ($this->name) {
            $file = dirname(__DIR__) . "/Template/socket";
            $template = str_replace("_SocketIndex_", $this->UC_word($this->name), file_get_contents($file));
            $this->create_file($this->UC_word($this->name), basename(dirname(__DIR__, 3)) . '/sockets', $template);
        } else Core::logMessage("Укажите имя для шаблона.");
    }
    
    private function mRepository(): void
    {
        if ($this->name && !strrpos($this->name, 'Repository')) {
            Core::logMessage("Укажите корректное имя шаблона.");
        }elseif ($this->name) {
            $file = dirname(__DIR__) . "/Template/repository";
            $template = str_replace("_RepositoryIndex_", $this->UC_word($this->name), file_get_contents($file));
            $template = str_replace("_RepositoryTable_", strtolower(str_replace('Repository', 's', $this->name)), $template);
            $this->create_file($this->UC_word($this->name), basename(dirname(__DIR__, 3)) . '/repository', $template);
        } else Core::logMessage("Укажите имя для шаблона.");
    }

    private function create_file($fName, $path, $code = ""): void
    {
        if (!is_dir($path)) mkdir($path);
        $file_name = "$path/$fName.php";
        if (!file_exists($file_name)) {
            $fp = fopen($file_name, "x");
            fwrite($fp, $code);
            fclose($fp);
            Core::logMessage("Шаблон '{$this->argument}' успешно создан.", 32);
        }else Core::logMessage("Шаблон \"{$this->argument}\" с наименованием '$fName' уже существует.");
    }

    private function UC_word(String $str): array|string
    {
        return str_replace(" ", "", ucwords(str_replace("_", " ", $str)));
    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":api          -  Создать Api-контроллер.");
        Core::logText(":controller   -  Создать контроллер для обработки запросов.");
        Core::logText(":model        -  Создать Model (слепок таблицы, базы данных).");
        Core::logText(":repository   -  Создать Repository для соединения с базой данных.");
        Core::logText(":socket       -  Создать Socket контроллер.");
        Core::logLabel("End");
    }

}
