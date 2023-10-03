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
        $this->dockerComposeCreate();
        $this->dockerFolderCreate();
    }

    private function dockerComposeCreate(): void
    {
        if (!file_exists(PATH_ROOT . '/docker-compose.yml')) {

            $code = file_get_contents($this->templatePath . '/Docker/docker-compose.yml');
            $code = str_replace("__project__", strtolower(basename(PATH_ROOT)), $code);

            $fp = fopen(PATH_ROOT . '/docker-compose.yml', "x");
            fwrite($fp, $code);
            fclose($fp);
            self::printMessage("File 'docker-compose' is created.", 32);

        } else self::printMessage("File 'docker-compose' is already exist.");
    }

    private function dockerFolderCreate(): void
    {
        if (!is_dir(PATH_ROOT . '/docker')) {

            // nginx
            if (mkdir(PATH_ROOT. '/docker/nginx', 0777, true)) {
                self::printMessage("Folder 'docker/nginx' is created.", 32);

                $code = file_get_contents($this->templatePath . '/Docker/nginx/Dockerfile');

                $fp = fopen(PATH_ROOT . '/docker/nginx/Dockerfile', "x");
                fwrite($fp, $code);
                fclose($fp);
                self::printMessage("File 'docker/nginx/Dockerfile' is created.", 32);

                $code = file_get_contents($this->templatePath . '/Docker/nginx/local.nginx.conf');
                $code = str_replace("__project__", strtolower(basename(PATH_ROOT)), $code);

                $fp = fopen(PATH_ROOT . '/docker/nginx/local.nginx.conf', "x");
                fwrite($fp, $code);
                fclose($fp);
                self::printMessage("File 'docker/nginx/local.nginx.conf' is created.", 32);

                $code = file_get_contents($this->templatePath . '/Docker/nginx/nginx.conf');
                $code = str_replace("__project__", strtolower(basename(PATH_ROOT)), $code);

                $fp = fopen(PATH_ROOT . '/docker/nginx/nginx.conf', "x");
                fwrite($fp, $code);
                fclose($fp);
                self::printMessage("File 'docker/nginx/nginx.conf' is created.", 32);

            }
            else self::printMessage("Folder 'docker/nginx' is dont created.", 31);

            // php
            if (mkdir(PATH_ROOT. '/docker/php', 0777, true)) {

                $code = file_get_contents($this->templatePath . '/Docker/php/Dockerfile');

                $fp = fopen(PATH_ROOT . '/docker/php/Dockerfile', "x");
                fwrite($fp, $code);
                fclose($fp);
                self::printMessage("File 'docker/php/Dockerfile' is created.", 32);

                self::printMessage("Folder 'docker/php' is created.", 32);

            } else self::printMessage("Folder 'docker/php' is dont created.", 31);

        } else self::printMessage("Folder 'docker' is already exist.");
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Cfg Help", $cl);

        self::printLabel("extra cfg [args...] -[flags...]", $cl);
        self::printMessage("args - command", $cl);
        self::print("env - project configuration file", $cl);
        self::print("docker - create docker configuration file", $cl);

        // env
        self::printLabel("env", $cl);
        self::printMessage("flags - selection additional to be action", $cl);
        self::print("i - create configuration file", $cl);
        self::print("l - show configuration file", $cl);
        self::printLabel("env", $cl);

        self::printTitle("Cfg Help", $cl);
    }

}