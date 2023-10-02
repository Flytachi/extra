<?php

namespace Extra\Console\Command;

use Extra\Console\Inc\Cmd;

class Storage extends Cmd
{
    public static string $title = "command storage control";
    private string $templatePath;

    public function handle(): void
    {
        self::printTitle("Storage", 32);
        $this->templatePath = dirname(__DIR__) . '/Template/Storage';

        if (
            count($this->args['flags']) > 0 || count($this->args['arguments']) > 1
        ) $this->resolution();
        else {
            self::printMessage("Enter argument or options");
            self::print("Example: extra storage init");
        }

        self::printTitle("Storage", 32);
    }

    private function resolution(): void
    {
        if (array_key_exists(1, $this->args['arguments'])) {
            switch ($this->args['arguments'][1]) {
                case 'init': $this->initArg(); break;
                default:
                    self::printMessage("Argument '{$this->args['arguments'][1]}' not found");
                    break;
            }
        }
    }

    private function initArg(): void
    {
        if (count($this->args['flags']) == 0) {
            $this->storageInit();
            $this->storageCacheInit();
            $this->storageLogInit();
        } else {
            foreach ($this->args['flags'] as $flag) {
                switch ($flag) {
                    case 's': $this->storageInit(); break;
                    case 'c': $this->storageCacheInit(); break;
                    case 'l': $this->storageLogInit(); break;
                    default:
                        self::printMessage("Undefined flag '{$flag}'");
                        break;
                }
            }
        }
    }

    private function storageInit(): void
    {
        if (!is_dir(PATH_STORAGE)) {
            if (mkdir(PATH_STORAGE, 0777, true)) {
                copy(
                    $this->templatePath . '/gitignoreStorage',
                    PATH_STORAGE . '/.gitignore'
                );
                self::printMessage("Folder 'storage' is created.", 32);
            } else self::printMessage("Folder 'storage' dont created.", 31);
        } else self::printMessage("Folder 'storage' is already exist.");
    }

    private function storageCacheInit(): void
    {
        if (!is_dir(PATH_CACHE)) {
            if (mkdir(PATH_CACHE, 0777, true)) {
                copy(
                    $this->templatePath . '/gitignoreStorageCache',
                    PATH_CACHE . '/.gitignore'
                );
                self::printMessage("Folder 'cache' is created.", 32);
            } else self::printMessage("Folder 'cache' dont created.", 31);
        } else self::printMessage("Folder 'cache' is already exist.");
    }

    private function storageLogInit(): void
    {
        if (!is_dir(PATH_LOG)) {
            if (mkdir(PATH_LOG, 0777, true)) {
                copy(
                    $this->templatePath . '/gitignoreStorageLogs',
                    PATH_LOG . '/.gitignore'
                );
                self::printMessage("Folder 'logs' is created.", 32);
            } else self::printMessage("Folder 'logs' dont created.", 31);
        } else self::printMessage("Folder 'logs' is already exist.");
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Storage Help", $cl);

        self::printLabel("extra storage [args...] -[flags...]", $cl);
        self::printMessage("args - command", $cl);
        self::print("init - create storage folders", $cl);

        // init
        self::printLabel("init", $cl);
        self::printMessage("flags - selection of folder to be action", $cl);
        self::print("s - folder storage", $cl);
        self::print("c - folder cache", $cl);
        self::print("l - folder logs", $cl);
        self::printLabel("init", $cl);

        self::printTitle("Storage Help", $cl);
    }

}