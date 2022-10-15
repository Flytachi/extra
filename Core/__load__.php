<?php
namespace Console;

use Error;

/**
 *  Ядро консоли
 */

class Core
{
    private mixed $argument_count;
    private mixed $arguments;
    private String $command_dir = "Command";

    function __construct($arg1 = null, $arg2 = null)
    {
        $this->argument_count = $arg1;
        $this->arguments = $arg2;
        $this->handle();
    }

    private function handle(): void
    {
        if ($this->argument_count > 1) $this->resolution();
        else $this->help();
    }

    private function resolution(): void
    {
        require dirname(__DIR__, 2) . '/defines.php';
        require dirname(__DIR__) . '/Function/Dependencies.php';
        
        foreach (glob(__DIR__."/$this->command_dir/*") as $filename) require_once $filename;

        try {
            if ($Class = stristr($this->arguments[1], ":", true)) {
                $Class_construct = "\__".$Class;
                $Arg = str_replace(":", "", stristr($this->arguments[1], ":"));
            }else {
                $Class_construct = "\__".ucfirst($this->arguments[1]);
                $Arg = null;
            }
            $Arg2 = $this->arguments[2] ?? null;
            new $Class_construct($Arg, $Arg2);
        } catch (Error) {
            echo "\033[31m"." Нет такой команды.\n";
        }
        echo "\033[0m";

    }

    private function help(): void
    {
        echo "\033[33m"." ===========> Welcome to Warframe <=========== \n";
        echo "\033[33m"." Доступные команды: \n";
        foreach (glob(__DIR__."/$this->command_dir/*") as $command) echo "\033[33m"."  " . mb_strtolower(substr(strstr(basename($command), '_'), 2, -4)) . "\n";
        echo "\033[33m"." ============================================= \n";
        echo "\033[0m";
    }

}

