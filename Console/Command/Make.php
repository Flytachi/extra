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
        array_shift($this->args['arguments']);

        if (count($this->args['arguments']) == 0) {
            self::printMessage("Enter the names of the generated templates");
            self::print("Example: extra make example");
            self::print("Help: extra make [--help or -h]");
        } elseif (!count($this->args['flags'])) {
            self::printMessage("Specify template types");
            self::print("Example: extra make -asrm example");
            self::print("Help: extra make [--help or -h]");
        } else $this->resolution();

        self::printTitle("Make", 32);
    }

    private function resolution(): void
    {
        foreach ($this->args['arguments'] as $templateName) {
            if (in_array('a', $this->args['flags']))
                $this->createApiController($templateName);
            if (in_array('c', $this->args['flags']))
                $this->createController($templateName);
            if (in_array('s', $this->args['flags']))
                $this->createService($templateName);
            if (in_array('r', $this->args['flags']))
                $this->createRepository($templateName);
            if (in_array('t', $this->args['flags']))
                $this->createStore($templateName);
            if (in_array('m', $this->args['flags']))
                $this->createModel($templateName);
            if (in_array('d', $this->args['flags']))
                $this->createDto($templateName);
            if (in_array('q', $this->args['flags']))
                $this->createRequest($templateName);
            if (in_array('j', $this->args['flags']))
                $this->createJob($templateName);
            if (in_array('k', $this->args['flags']))
                $this->createKube($templateName);
            if (in_array('w', $this->args['flags']))
                $this->createWebSocket($templateName);
            if (in_array('n', $this->args['flags']))
                $this->createCmd($templateName);
        }
    }

    private function createApiController(string $name): void
    {
        $shortName = $name;
        $name = $this->ucWord($name) . 'Controller';
        $templatePath = $this->templatePath . '/ApiTemplate';
        $path = $this->getPath('Controllers');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);
        $code = str_replace("__shortName__", lcfirst($shortName), $code);

        $this->createFile($name, $path, $code, 'api');
    }

    private function createController(string $name): void
    {
        $shortName = $name;
        $name = $this->ucWord($name) . 'Controller';
        $templatePath = $this->templatePath . '/ControllerTemplate';
        $path = $this->getPath('Controllers');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);
        $code = str_replace("__shortName__", lcfirst($shortName), $code);

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
        $path = $this->getPath('Providers/Repositories');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);
        $code = str_replace("__tableName__", strtolower(str_replace('Repository', 's', $name)), $code);

        $this->createFile($name, $path, $code, 'repository');
    }

    private function createStore(string $name): void
    {
        $name = $this->ucWord($name) . 'Store';
        $templatePath = $this->templatePath . '/StoreRedisTemplate';
        $path = $this->getPath('Providers/RStores');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);
        $code = str_replace("__dbIndexName__", strtolower(str_replace('Store', '', $name)), $code);

        $this->createFile($name, $path, $code, 'store');
    }

    private function createModel(string $name): void
    {
        $name = $this->ucWord($name) . 'Model';
        $templatePath = $this->templatePath . '/ModelTemplate';
        $path = $this->getPath('Entity/Models');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'model');
    }

    private function createDto(string $name): void
    {
        $name = $this->ucWord($name) . 'Dto';
        $templatePath = $this->templatePath . '/DtoTemplate';
        $path = $this->getPath('Entity/Dto');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'dto');
    }

    private function createRequest(string $name): void
    {
        $name = $this->ucWord($name) . 'Request';
        $templatePath = $this->templatePath . '/RequestTemplate';
        $path = $this->getPath('Entity/Requests');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'request');
    }

    private function createJob(string $name): void
    {
        $name = $this->ucWord($name) . 'Job';
        $templatePath = $this->templatePath . '/JobTemplate';
        $path = $this->getPath('Threads/Jobs');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'job');
    }

    private function createKube(string $name): void
    {
        $name = $this->ucWord($name) . 'Kube';
        $templatePath = $this->templatePath . '/KubeTemplate';
        $path = $this->getPath('Threads/Kube');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'kube');
    }

    private function createWebSocket(string $name): void
    {
        $name = $this->ucWord($name) . 'WebSocket';
        $templatePath = $this->templatePath . '/WebSocketTemplate';
        $path = $this->getPath('Threads/Socket');

        $code = file_get_contents($templatePath);
        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'socket');
    }

    private function createCmd(string $name): void
    {
        $name = $this->ucWord($name);
        $templatePath = $this->templatePath . '/CmdTemplate';
        $path = $this->getPath('Command');

        $code = file_get_contents($templatePath);
        $code = str_replace("__className__", $name, $code);

        $this->createFile($name, $path, $code, 'cmd');
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
        self::printMessage("args - names of generated templates", $cl);
        self::printMessage("flags - selection of templates to be created", $cl);
        self::print("a - Template ApiBase, prefix Controller", $cl);
        self::print("c - Template ControllerBase, prefix Controller", $cl);
        self::print("", $cl);
        self::print("m - Template ModelBase, prefix Model", $cl);
        self::print("d - Template DtoObject, prefix Dto", $cl);
        self::print("q - Template RequestObject, prefix Request", $cl);
        self::print("", $cl);
        self::print("s - Template Service, prefix Service", $cl);
        self::print("r - Template Repository, prefix Repository", $cl);
        self::print("t - Template Store, prefix Store", $cl);
        self::print("", $cl);
        self::print("j - Template Job, prefix Job", $cl);
        self::print("k - Template Kube, prefix Kube", $cl);
        self::print("w - Template WebSocket, prefix WebSocket", $cl);
        self::print("", $cl);
        self::print("n - Template CustomCmd, dont prefix", $cl);
        self::printMessage("options - additional option", $cl);
        self::print("folder - folder where template will be added (recursive creation is used)", $cl);

        self::printTitle("Make Help", $cl);
    }

}