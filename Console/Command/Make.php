<?php

namespace Extra\Console\Command;

use Extra\Console\Inc\Cmd;

class Make extends Cmd
{
    public static string $title = "command for creating templates";
    private string $templatePath;

    public function handle(): void
    {
        self::printTitle("Make", 32);
        $this->templatePath = dirname(__DIR__) . '/Template/Make';

        if (!array_key_exists(1, $this->args['arguments'])) {
            self::printMessage("Enter a name for the template");
            self::print("Example: extra make example");
            self::print("Help: extra make [--help or -h]");
        } elseif (!count($this->args['flags'])) {
            self::printMessage("Specify template types");
            self::print("Example: extra make -acsrm example");
            self::print("Help: extra make [--help or -h]");
        } else $this->resolution();

        self::printTitle("Make", 32);
    }

    private function resolution(): void
    {
        if (in_array('a', $this->args['flags']))
            $this->createApiController($this->args['arguments'][1]);
        if (in_array('c', $this->args['flags']))
            $this->createController($this->args['arguments'][1]);
        if (in_array('s', $this->args['flags']))
            $this->createService($this->args['arguments'][1]);
        if (in_array('r', $this->args['flags']))
            $this->createRepository($this->args['arguments'][1]);
        if (in_array('m', $this->args['flags']))
            $this->createModel($this->args['arguments'][1]);
    }

    private function createApiController(string $name): void
    {
        $name = $this->ucWord($name) . 'Controller';
        $templatePath = $this->templatePath . '/ApiTemplate';
        $path = $this->getPath('Controllers');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'api');
    }

    private function createController(string $name): void
    {
        $name = $this->ucWord($name) . 'Controller';
        $templatePath = $this->templatePath . '/ControllerTemplate';
        $path = $this->getPath('Controllers');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'controller');
    }

    private function createService(string $name): void
    {
        $name = $this->ucWord($name) . 'Service';
        $templatePath = $this->templatePath . '/ServiceTemplate';
        $path = $this->getPath('Services');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'service');
    }

    private function createRepository(string $name): void
    {
        $name = $this->ucWord($name) . 'Repository';
        $templatePath = $this->templatePath . '/RepositoryTemplate';
        $path = $this->getPath('Repositories');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);
        $code = str_replace("__tableName__", strtolower(str_replace('Repository', 's', $name)), $code);

        $this->createFile($name, $path, $code, 'repository');
    }

    private function createModel(string $name): void
    {
        $name = $this->ucWord($name) . 'Model';
        $templatePath = $this->templatePath . '/ModelTemplate';
        $path = $this->getPath('Models');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'model');
    }


    private function createFile(string $fName, string $path, string $code = "", ?string $prefix = null): void
    {
        $path = PATH_APP . $path;
        $prefix = ($prefix) ? " ({$prefix})" : '';
        if (!is_dir($path)) mkdir($path, 0777, true);
        $fileName = "$path/$fName.php";
        if (!file_exists($fileName)) {
            $fp = fopen($fileName, "x");
            fwrite($fp, $code);
            fclose($fp);
            self::printMessage("{$fName} file created successfully.{$prefix}", 32);
        } else self::printMessage("The {$fName} file already exist.{$prefix}");
    }

    private function ucWord(string $str): string
    {
        return str_replace(" ", "", ucwords(str_replace("_", " ", $str)));
    }

    private function getPath(string $mainFolder): string
    {
        return '/' . $mainFolder . (
            (
                array_key_exists('folder', $this->args['options'])
                && is_string($this->args['options']['folder'])
            ) ? '/' . $this->args['options']['folder'] : '');
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Make Help", $cl);

        self::printLabel("extra make [args...] -[flags...] --[options...]", $cl);
        self::printMessage("args - template name", $cl);
        self::printMessage("flags - selection of templates to be created", $cl);
        self::print("a - Template ApiBase, prefix Controller", $cl);
        self::print("c - Template ControllerBase, prefix Controller", $cl);
        self::print("s - Template Service, prefix Service", $cl);
        self::print("r - Template Repository, prefix Repository", $cl);
        self::print("m - Template ModelBase, prefix Model", $cl);
        self::printMessage("options - additional option", $cl);
        self::print("folder - folder where template will be added (recursive creation is used)", $cl);

        self::printTitle("Make Help", $cl);
    }
}