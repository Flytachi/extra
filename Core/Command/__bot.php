<?php

class __Bot
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
            if ($this->argument == "register") $this->register();
            // elseif ($this->argument == "init") $this->init();
            else echo "\033[33m". " Команды '$this->argument' не существует!\n";
        } catch (Error $e) {
            echo "\033[31m"." Ошибка в скрипте.\n";
        }
        
    }

    private function register(): void
    {
        if ($this->name) {
            dd($this->name);
            // $get = [
            //     'url'  => $this->name,
            // ];
             
            // $ch = curl_init('https://api.telegram.org/bot?' . http_build_query($get));
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($ch, CURLOPT_HEADER, false);
            // $html = curl_exec($ch);
            // curl_close($ch);
             
            // echo $html;
            
        }
    }

    private function help(): void
    {
        echo "\033[33m"." =======> Help <======= \n";
        echo "\033[33m"."  :init         -  Создать контроллер для обработки запросов.\n";
        echo "\033[33m"."  :register     -  Создать Api-контроллер.\n";
        echo "\033[33m"." =======> Help <======= \n";
    }

}
