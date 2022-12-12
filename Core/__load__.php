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
                $Class = $this->arguments[1];
                $Class_construct = "\__".ucfirst($this->arguments[1]);
                $Arg = null;
            }

            $Arg2 = $this->arguments[2] ?? null;
            $this->logStart($Class . (($Arg) ? ' -> ' . $Arg : ''));
            new $Class_construct($Arg, $Arg2);

        } catch (Error $e) {
            Core::logMessage("Нет такой команды.", 31);
        }
        echo "\033[0m";

    }

    private function help(): void
    {
        self::logTitle("Welcome to Warframe", 32);
        self::loglabel("Доступные команды:", 32);
        foreach (glob(__DIR__."/$this->command_dir/*") as $command) self::logText(mb_strtolower(substr(strstr(basename($command), '_'), 2, -4)), 32);
        self::logTitle("===================", 32);
    }

    public static function logStart(string $arg): void
    {
        echo "\033[32m"." |===> {$arg}\n";
        echo "\033[0m";
    }

    public static function logTitle(string $message, int $cl = 33): void
    {
        echo "\033[" . $cl . "m"." | [================ $message ================]\n";
        echo "\033[0m";
    }

    public static function logLabel(string $message, int $cl = 33): void
    {
        echo "\033[" . $cl . "m"." | [ $message ]\n";
        echo "\033[0m";
    }

    public static function logText(string $message, int $cl = 33): void
    {
        echo "\033[" . $cl . "m"." |\t $message \n";
        echo "\033[0m";
    }

    public static function logTextSplit(string $message = '', int $cl = 33): void
    {
        if ($message) {
            foreach (explode(PHP_EOL, $message) as $str)
                echo "\033[" . $cl . "m"." |\t $str \n";
        } else echo "\033[" . $cl . "m"." |\t Нет данных \n";
        echo "\033[0m";
    }

    public static function logMessage(string $message, int $cl = 33): void
    {
        echo "\033[" . $cl . "m"." |==> $message \n";
        echo "\033[0m";
    }

}

