<?php

declare(strict_types=1);

namespace Flytachi\Extra\Console\Command;

use Flytachi\Extra\Console\Inc\Cmd;

class Help extends Cmd
{
    public static string $title = "command reference";

    public function handle(): void
    {
        if (array_key_exists(1, $this->args['arguments'])) {
            $this->resolution($this->args['arguments'][1]);
        } else {
            $this->list();
        }
    }

    private function resolution(string $cmdName): void
    {
        $cmd = ucwords($cmdName);
        ('Extra\Console\Command\\' . $cmd)::help();
    }

    public function list(): void
    {
        self::printTitle("Extra Help", 34);
        self::print("Extra Version: 5.1", 34);
        self::print("PHP Version: " . PHP_VERSION, 34);
        self::print("OS: " . PHP_OS_FAMILY . DIRECTORY_SEPARATOR . PHP_OS, 34);
        self::print("SAPI: " . PHP_SAPI, 34);

        self::printLabel("Commands", 34);
        foreach (glob(__DIR__ . '/*.php') as $cmdFile) {
            $name = basename($cmdFile, '.php');
            self::printMessage(
                strtolower($name)
                . " - " .  ('Flytachi\Extra\Console\Command\\' . $name)::$title,
                34
            );
        }
        self::printTitle("Extra Help", 34);
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Make Help", $cl);

        self::printLabel("extra help [args...]", $cl);
        self::printMessage("args - command name", $cl);

        self::printTitle("Make Help", $cl);
    }
}
