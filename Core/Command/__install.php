<?php

class __Install
{
    private $argument;
    private $name;
    private $path = "tools/libs";

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
            if ($this->argument == "npm") echo exec("npm install");
            elseif($this->argument == "git") {
                require_once dirname(__DIR__, 3) . '/tools/variables.php';
                foreach ($git_links as $link => $folder) echo exec("git clone $link $this->path/$folder");
            }
        } catch (\Error $e) {
            echo "\033[31m"." Ошибка в скрипте.\n";
        }
    }

    public function help()
    {
        echo "\033[33m"." =======> Help <======= \n";
        echo "\033[33m"."  :git      -  установить компоненты \"git\".\n";
        echo "\033[33m"."  :npm      -  установить компоненты \"npm\".\n";
        echo "\033[33m"." =======> Help <======= \n";
    }

}

?>