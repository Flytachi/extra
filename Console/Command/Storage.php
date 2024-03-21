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
                case 'clean': $this->cleanArg(); break;
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

    private function cleanArg(): void
    {
        if (count($this->args['flags']) == 0) {
            $this->storageClean();
            $this->storageCacheClean();
            $this->storageLogClean();
        } else {
            foreach ($this->args['flags'] as $flag) {
                switch ($flag) {
                    case 's': $this->storageClean(); break;
                    case 'c': $this->storageCacheClean(); break;
                    case 'l': $this->storageLogClean(); break;
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
                self::printMessage("Folder 'storage/cache' is created.", 32);
            } else self::printMessage("Folder 'storage/cache' dont created.", 31);
        } else self::printMessage("Folder 'storage/cache' is already exist.");
    }

    private function storageLogInit(): void
    {
        if (!is_dir(PATH_LOG)) {
            if (mkdir(PATH_LOG, 0777, true)) {
                copy(
                    $this->templatePath . '/gitignoreStorageLogs',
                    PATH_LOG . '/.gitignore'
                );
                self::printMessage("Folder 'storage/logs' is created.", 32);
            } else self::printMessage("Folder 'storage/logs' dont created.", 31);
        } else self::printMessage("Folder 'storage/logs' is already exist.");
    }

    private function storageClean(): void
    {
        if (is_dir(PATH_STORAGE)) {
            flushDirectory(PATH_STORAGE, PATH_STORAGE, [
                str_replace(PATH_STORAGE, '', PATH_CACHE),
                str_replace(PATH_STORAGE, '', PATH_LOG)
            ], ['.gitignore'],
                function($info) {
                    $type = $info['is_dir'] ? 'Folder' : 'File';
                    if ($info['status'])
                        self::printMessage("STORAGE: {$type} '{$info['path']}' has been successfully deleted.", 32);
                    else self::printMessage("STORAGE: {$type} '{$info['path']}' could not be deleted.");
                }
            );
        } else self::printMessage("Folder 'storage' does not exist.");
    }

    private function storageCacheClean(): void
    {
        if (is_dir(PATH_CACHE)) {
            flushDirectory(PATH_CACHE, PATH_CACHE, [], ['.gitignore'],
                function($info) {
                    $type = $info['is_dir'] ? 'Folder' : 'File';
                    if ($info['status'])
                        self::printMessage("CACHE: {$type} '{$info['path']}' has been successfully deleted.", 32);
                    else self::printMessage("CACHE: {$type} '{$info['path']}' could not be deleted.");
                }
            );
        } else self::printMessage("Folder 'cache' does not exist.");
    }

    private function storageLogClean(): void
    {
        if (is_dir(PATH_LOG)) {
            flushDirectory(PATH_LOG, PATH_LOG, [], ['.gitignore'],
                function($info) {
                    $type = $info['is_dir'] ? 'Folder' : 'File';
                    if ($info['status'])
                        self::printMessage("LOG: {$type} '{$info['path']}' has been successfully deleted.", 32);
                    else self::printMessage("LOG: {$type} '{$info['path']}' could not be deleted.");
                }
            );
        } else self::printMessage("Folder 'logs' does not exist.");
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Storage Help", $cl);

        self::printLabel("extra storage [args...] -[flags...]", $cl);
        self::printMessage("args - command", $cl);
        self::print("init - create storage folders", $cl);
        self::print("clean - clean storage folders", $cl);

        // init
        self::printLabel("init", $cl);
        self::printMessage("flags - selection of folder to be action", $cl);
        self::print("s - create folder storage", $cl);
        self::print("c - create folder storage/cache", $cl);
        self::print("l - create folder storage/logs", $cl);
        self::printLabel("init", $cl);

        // clean
        self::printLabel("clean", $cl);
        self::printMessage("flags - selection of folder to be action", $cl);
        self::print("s - clean folder storage", $cl);
        self::print("c - clean folder storage/cache", $cl);
        self::print("l - clean folder storage/logs", $cl);
        self::printLabel("clean", $cl);

        self::printTitle("Storage Help", $cl);
    }

}