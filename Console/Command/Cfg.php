<?php

namespace Extra\Console\Command;

use Extra\Console\Inc\Cmd;

class Cfg extends Cmd
{
    public static string $title = "command config control";
    private string $templatePath;

    public function handle(): void
    {
        self::printTitle("Cfg", 32);
        $this->templatePath = dirname(__DIR__) . '/Template';

        if (
            count($this->args['arguments']) > 1
        ) $this->resolution();
        else {
            self::printMessage("Enter argument");
            self::print("Example: extra cfg env");
        }

        self::printTitle("Cfg", 32);
    }

    private function resolution(): void
    {
        if (array_key_exists(1, $this->args['arguments'])) {
            switch ($this->args['arguments'][1]) {
                case 'env': $this->envArg(); break;
                case 'docker': $this->dockerArg(); break;
                case 'postman': $this->postmanArg(); break;
                default:
                    self::printMessage("Argument '{$this->args['arguments'][1]}' not found");
                    break;
            }
        }
    }

    private function envArg(): void
    {
        if (in_array('i', $this->args['flags'])) $this->envCreate();
        if (in_array('l', $this->args['flags'])) $this->envShow();
    }

    private function envCreate(): void
    {
        if (!file_exists(PATH_ROOT . '/.env')) {

            if (copy(
                $this->templatePath . '/Build/env',
                PATH_ROOT . '/.env'
            )) self::printMessage("File '.env' is created.", 32);
            else self::printMessage("File '.env' dont created.", 31);

        } else self::printMessage("File '.env' is already exist.");
    }

    private function envShow(): void
    {
        self::printLabel(ENV_PATH, 34);
        self::printSplit(file_get_contents(ENV_PATH), 34);
        self::printLabel(ENV_PATH, 34);
    }

    private function dockerArg(): void
    {
        multiCopy($this->templatePath . '/Docker', PATH_ROOT);
        self::printMessage("Folder 'docker' is created.", 32);
        self::printMessage("File 'docker-compose' is created.", 32);
        self::printMessage("File 'Dockerfile' is created.", 32);
    }

    private function postmanArg(): void
    {
        $this->postmanCreate();
    }

    private function postmanCreate(): void
    {
        if (!is_dir(PATH_APP . '/Controllers'))
            mkdir(PATH_APP . '/Controllers', 0777, true);
        if (!file_exists(PATH_APP . '/Controllers/PostmanController.php')) {

            $code = file_get_contents($this->templatePath . '/Packages/PostmanTemplate');
            $fp = fopen(PATH_APP . '/Controllers/PostmanController.php', "x");
            fwrite($fp, $code);
            fclose($fp);
            self::printMessage("File 'PostmanController' is created.", 32);

        } else self::printMessage("File 'PostmanController' is already exist.");
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Cfg Help", $cl);

        self::printLabel("extra cfg [args...] -[flags...]", $cl);
        self::printMessage("args - command", $cl);
        self::print("env - project configuration file", $cl);
        self::print("docker - create docker configuration file", $cl);
        self::print("postman - create postman collection api controller", $cl);

        // env
        self::printLabel("env", $cl);
        self::printMessage("flags - selection additional to be action", $cl);
        self::print("i - create configuration file", $cl);
        self::print("l - show configuration file", $cl);
        self::printLabel("env", $cl);

        self::printTitle("Cfg Help", $cl);
    }

}