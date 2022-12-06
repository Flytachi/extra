<?php

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
            else echo "\033[33m". " Шаблона '$this->argument' не существует!\n";
        } catch (Error) {
           echo "\033[31m"." Ошибка в скрипте.\n";
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
        if (!strrpos($this->name, 'Api')) {
            echo "\033[33m". " Укажите корректное имя шаблона!\n";
        }elseif ($this->name) {
            $file = dirname(__DIR__) . "/Template/api";
            $template = str_replace("_ApiIndex_", $this->UC_word($this->name), file_get_contents($file));
            $this->create_file($this->UC_word($this->name), basename(dirname(__DIR__, 3)) . '/api', $template);
        } else echo "\033[33m". " Укажите имя для шаблона!\n";
    }

    private function mController(): void
    {
        if (!strrpos($this->name, 'Controller')) {
            echo "\033[33m". " Укажите корректное имя шаблона!\n";
        }elseif ($this->name) {
            $file = dirname(__DIR__) . "/Template/controller";
            $template = str_replace("_ControllerIndex_", $this->UC_word($this->name), file_get_contents($file));
            $this->create_file($this->UC_word($this->name), basename(dirname(__DIR__, 3)) . '/controllers', $template);
        } else echo "\033[33m". " Укажите имя для шаблона!\n";
    }

    private function mModel(): void
    {
        if (!strrpos($this->name, 'Model')) {
            echo "\033[33m". " Укажите корректное имя шаблона!\n";
        }elseif ($this->name) {
            $file = dirname(__DIR__) . "/Template/model";
            $template = str_replace("_ModelIndex_", $this->UC_word($this->name), file_get_contents($file));
            $this->create_file($this->UC_word($this->name), basename(dirname(__DIR__, 3)) . '/models', $template);
        } else echo "\033[33m". " Укажите имя для шаблона!\n";
    }

    private function mSocket(): void
    {
        if (!strrpos($this->name, 'Socket')) {
            echo "\033[33m". " Укажите корректное имя шаблона!\n";
        }elseif ($this->name) {
            $file = dirname(__DIR__) . "/Template/socket";
            $template = str_replace("_SocketIndex_", $this->UC_word($this->name), file_get_contents($file));
            $this->create_file($this->UC_word($this->name), basename(dirname(__DIR__, 3)) . '/sockets', $template);
        } else echo "\033[33m". " Укажите имя для шаблона!\n";
    }
    
    private function mRepository(): void
    {
        if (!strrpos($this->name, 'Repository')) {
            echo "\033[33m". " Укажите корректное имя шаблона!\n";
        }elseif ($this->name) {
            $file = dirname(__DIR__) . "/Template/repository";
            $template = str_replace("_RepositoryIndex_", $this->UC_word($this->name), file_get_contents($file));
            $template = str_replace("_RepositoryTable_", strtolower(str_replace('Repository', 's', $this->name)), $template);
            $template = str_replace("_RepositoryModel_", $this->UC_word(str_replace('Repository', 'Model', $this->name)), $template);
            $this->create_file($this->UC_word($this->name), basename(dirname(__DIR__, 3)) . '/repository', $template);
        } else echo "\033[33m". " Укажите имя для шаблона!\n";
    }

    private function create_file($fName, $path, $code = ""): void
    {
        if (!is_dir($path)) mkdir($path);
        $file_name = "$path/$fName.php";
        if (!file_exists($file_name)) {
            $fp = fopen($file_name, "x");
            fwrite($fp, $code);
            fclose($fp);
            echo "\033[32m"." Шаблон '$this->argument' успешно создан.\n";
        }else echo "\033[33m"." Шаблон \"$this->argument\" с наименованием '$fName' уже существует.\n";
    }

    private function UC_word(String $str): array|string
    {
        return str_replace(" ", "", ucwords(str_replace("_", " ", $str)));
    }

    private function help(): void
    {
        echo "\033[33m"." =======> Help <======= \n";
        echo "\033[33m"."  :api          -  Создать Api-контроллер.\n";
        echo "\033[33m"."  :controller   -  Создать контроллер для обработки запросов.\n";
        echo "\033[33m"."  :model        -  Создать Model (слепок таблицы, базы данных).\n";
        echo "\033[33m"."  :repository   -  Создать Repository для соединения с базой данных.\n";
        echo "\033[33m"."  :socket       -  Создать Socket контроллер.\n";
        echo "\033[33m"." =======> Help <======= \n";
    }

}
