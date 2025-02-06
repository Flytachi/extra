<?php

declare(strict_types=1);

namespace Flytachi\Extra\Console\Command;

use Flytachi\Extra\Console\Inc\Cmd;
use Flytachi\Extra\Extra;

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
        } else {
            $this->resolution();
        }

        self::printTitle("Make", 32);
    }

    private function resolution(): void
    {
        foreach ($this->args['arguments'] as $templateName) {
            $templateName = str_replace('.', '/', $templateName);
            if (in_array('a', $this->args['flags'])) {
                $this->createRestController($templateName);
            }
            if (in_array('c', $this->args['flags'])) {
                $this->createController($templateName);
            }
            if (in_array('s', $this->args['flags'])) {
                $this->createService($templateName);
            }
            if (in_array('r', $this->args['flags'])) {
                $this->createRepository($templateName);
            }
            if (in_array('t', $this->args['flags'])) {
                $this->createStore($templateName);
            }
            if (in_array('m', $this->args['flags'])) {
                $this->createModel($templateName);
            }
            if (in_array('d', $this->args['flags'])) {
                $this->createDto($templateName);
            }
            if (in_array('q', $this->args['flags'])) {
                $this->createRequest($templateName);
            }
            if (in_array('j', $this->args['flags'])) {
                $this->createJob($templateName);
            }
            if (in_array('k', $this->args['flags'])) {
                $this->createKube($templateName);
            }
            if (in_array('w', $this->args['flags'])) {
                $this->createWebSocket($templateName);
            }
            if (in_array('n', $this->args['flags'])) {
                $this->createCmd($templateName);
            }
        }
    }

    private function createRestController(string $name): void
    {
        $info = $this->getInfo($name, 'Controller', 'RestControllerTemplate');
        $code = file_get_contents($info['template']);
        $code = str_replace("__namespace__", $info['namespace'], $code);
        $code = str_replace("__className__", $info['className'], $code);
        $code = str_replace("__shortName__", lcfirst(str_replace('Controller', '', $info['className'])), $code);
        $this->createFile($info['className'], $info['path'], $code, 'rest');
    }

    private function createController(string $name): void
    {
        $info = $this->getInfo($name, 'Controller', 'ControllerTemplate');
        $code = file_get_contents($info['template']);
        $code = str_replace("__namespace__", $info['namespace'], $code);
        $code = str_replace("__className__", $info['className'], $code);
        $code = str_replace("__shortName__", lcfirst(str_replace('Controller', '', $info['className'])), $code);
        $this->createFile($info['className'], $info['path'], $code, 'controller');
    }

    private function createService(string $name): void
    {
        $info = $this->getInfo($name, 'Service', 'ServiceTemplate');
        $code = file_get_contents($info['template']);
        $code = str_replace("__namespace__", $info['namespace'], $code);
        $code = str_replace("__className__", $info['className'], $code);
        $this->createFile($info['className'], $info['path'], $code, 'service');
    }

    private function createRepository(string $name): void
    {
        $info = $this->getInfo($name, 'Repository', 'RepositoryTemplate');
        $code = file_get_contents($info['template']);
        $code = str_replace("__namespace__", $info['namespace'], $code);
        $code = str_replace("__className__", $info['className'], $code);
        $code = str_replace("__tableName__", strtolower(str_replace('Repository', 's', $info['className'])), $code);
        $this->createFile($info['className'], $info['path'], $code, 'repository');
    }

    private function createStore(string $name): void
    {
        $info = $this->getInfo($name, 'Store', 'StoreRedisTemplate');
        $code = file_get_contents($info['template']);
        $code = str_replace("__namespace__", $info['namespace'], $code);
        $code = str_replace("__className__", $info['className'], $code);
        $this->createFile($info['className'], $info['path'], $code, 'store');
    }

    private function createModel(string $name): void
    {
        $info = $this->getInfo($name, 'Model', 'ModelTemplate');
        $code = file_get_contents($info['template']);
        $code = str_replace("__namespace__", $info['namespace'], $code);
        $code = str_replace("__className__", $info['className'], $code);
        $this->createFile($info['className'], $info['path'], $code, 'model');
    }

    private function createDto(string $name): void
    {
        $info = $this->getInfo($name, 'Dto', 'DtoTemplate');
        $code = file_get_contents($info['template']);
        $code = str_replace("__namespace__", $info['namespace'], $code);
        $code = str_replace("__className__", $info['className'], $code);
        $this->createFile($info['className'], $info['path'], $code, 'dto');
    }

    private function createRequest(string $name): void
    {
        $info = $this->getInfo($name, 'Request', 'RequestTemplate');
        $code = file_get_contents($info['template']);
        $code = str_replace("__namespace__", $info['namespace'], $code);
        $code = str_replace("__className__", $info['className'], $code);
        $this->createFile($info['className'], $info['path'], $code, 'request');
    }

//    private function createJob(string $name): void
//    {
//        $name = $this->ucWord($name) . 'Job';
//        $templatePath = $this->templatePath . '/JobTemplate';
//        $path = $this->getPath('Threads/Jobs');
//
//        $code = file_get_contents($templatePath);
//        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
//        $code = str_replace("__className__", $name, $code);
//
//        $this->createFile($name, $path, $code, 'job');
//    }
//
//    private function createKube(string $name): void
//    {
//        $name = $this->ucWord($name) . 'Kube';
//        $templatePath = $this->templatePath . '/KubeTemplate';
//        $path = $this->getPath('Threads/Kube');
//
//        $code = file_get_contents($templatePath);
//        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
//        $code = str_replace("__className__", $name, $code);
//
//        $this->createFile($name, $path, $code, 'kube');
//    }
//
//    private function createWebSocket(string $name): void
//    {
//        $name = $this->ucWord($name) . 'WebSocket';
//        $templatePath = $this->templatePath . '/WebSocketTemplate';
//        $path = $this->getPath('Threads/Socket');
//
//        $code = file_get_contents($templatePath);
//        $code = str_replace("__namespace__", str_replace('/', '\\', trim($path, " \t\n\r\0\x0B/")), $code);
//        $code = str_replace("__className__", $name, $code);
//
//        $this->createFile($name, $path, $code, 'socket');
//    }
//
    private function createCmd(string $name): void
    {
        $info = $this->getInfo($name, 'Cmd', 'CmdTemplate');
        $code = file_get_contents($info['template']);
        $code = str_replace("__namespace__", $info['namespace'], $code);
        $code = str_replace("__className__", $info['className'], $code);
        $this->createFile($info['className'], $info['path'], $code, 'cmd');
    }


    private function createFile(string $fName, string $path, string $code = "", ?string $prefix = null): void
    {
        $path = Extra::$pathApp . $path;
        $prefix = ($prefix) ? " ({$prefix})" : '';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $fileName = "$path/$fName.php";
        if (!file_exists($fileName)) {
            $fp = fopen($fileName, "x");
            fwrite($fp, $code);
            fclose($fp);
            self::printMessage("{$fName} file created successfully.{$prefix}", 32);
        } else {
            self::printMessage("The {$fName} file already exist.{$prefix}");
        }
    }

    private function getInfo(string $way, string $prefix, string $templateName): array
    {
        $root = ucwords(basename(Extra::$pathApp));
        $way = $this->ucWord($way) . $prefix;
        $className = basename($way);
        $way = str_replace($className, '', $way);
        return [
            'namespace' => str_replace('/', '\\', trim($root . '/' . $way, " \t\n\r\0\x0B/")),
            'className' => $className,
            'path' => '/' . $way,
            'template' => $this->templatePath . '/' . $templateName,
        ];
    }

    private function ucWord(string $str): string
    {
        return str_replace(" ", "", ucwords(str_replace("_", " ", $str)));
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Make Help", $cl);

        self::printLabel("extra make [args...] -[flags...] --[options...]", $cl);
        self::printMessage("args - names of generated templates", $cl);
        self::printMessage("flags - selection of templates to be created", $cl);
        self::print("a - Template RestController, prefix RestController", $cl);
        self::print("c - Template Controller, prefix Controller", $cl);
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
        self::printTitle("Make Help", $cl);
    }
}
