<?php

declare(strict_types=1);

namespace Flytachi\Extra\Console\Command;

use Flytachi\Extra\Console\Inc\Cmd;
use Flytachi\Extra\Extra;

class Run extends Cmd
{
    public static string $title = "command runnable control";
    public final const string HOST = '0.0.0.0';
    public final const int PORT = 8000;

    public function handle(): void
    {
        self::printTitle("Run", 32);

        if (
            count($this->args['arguments']) > 1
        ) {
            $this->resolution();
        } else {
            self::printMessage("Enter argument");
            self::print("Example: extra run serve");
        }

        self::printTitle("Run", 32);
    }

    private function resolution(): void
    {
        if (array_key_exists(1, $this->args['arguments'])) {
            switch ($this->args['arguments'][1]) {
                case 'serve':
                    $this->serveArg();
                    break;
                case 'script':
                    $this->scriptArg();
                    break;
                case 'thread':
                    $this->threadArg();
                    break;
                default:
                    self::printMessage("Argument '{$this->args['arguments'][1]}' not found");
                    break;
            }
        }
    }

    private function serveArg(): void
    {
        $host = (isset($this->args['options']['host'])) ? $this->args['options']['host'] : self::HOST;
        $port = (isset($this->args['options']['port'])) ? (int) $this->args['options']['port'] : self::PORT;
        $connection = @fsockopen($host, $port);

        if (is_resource($connection)) {
            self::printMessage("Permission denied, 'http://{$host}:{$port}' is already busy!");
            fclose($connection);
        } else {
            self::printMessage("Starting the server to 'http://" . $host . ':' . $port . "'", 32);
            exec("php -S {$host}:{$port} -t " . Extra::$pathPublic);
        }
    }

    private function scriptArg(): void
    {
        self::printMessage("Script currently in development");
//        if (array_key_exists(2, $this->args['arguments'])) {
//            $name = ucwords($this->args['arguments'][2]);
//            $classname = "Command\\" . $name;
//            if (!class_exists($classname)) {
//                self::printMessage("Script named '{$name}' not found.");
//            } else {
//                $classname::script([
//                    'arguments' => array_values(array_slice($this->args['arguments'], 2)),
//                    'options' => $this->args['options'],
//                    'flags' => $this->args['flags'],
//                ]);
//            }
//        } else {
//            self::printMessage("Script name not specified.");
//        }
    }

    private function threadArg(): void
    {
        if (extension_loaded('pcntl') && pcntl_async_signals()) {
            if (
                array_key_exists('class-name', $this->args['options'])
                && $this->args['options']['class-name']
            ) {
                $class = $this->args['options']['class-name'];
                if (class_exists($class)) {
                    if (
                        array_key_exists(0, $this->args['flags'])
                        && $this->args['flags'][0] == 'd'
                    ) {
                        $this->threadRunnableToBack($class);
                    } else {
                        $this->threadRunnable($class);
                    }
                } else {
                    self::printMessage("The specified class '{$class}' was not found");
                }
            } else {
                self::printMessage("class-name option not specified");
            }
        } else {
            self::printMessage("Asynchronous pcntl signals are not enabled", 31);
        }
    }

    private function threadRunnable(string $class): void
    {
        // Cache Data
        $data = null;
        if (array_key_exists('class-cache', $this->args['options'])) {
            $filePath = Extra::$pathStorageCache . '/' . $this->args['options']['class-cache'];
            if (is_file($filePath)) {
                $data = unserialize(file_get_contents($filePath));
                unlink($filePath);
            }
        }

        self::printMessage("{$class} start", 32);
        ($class)::start($data);
        self::printMessage("{$class} end", 32);
    }

    private function threadRunnableToBack(string $class): void
    {
        // Cache
        $cache = null;
        if (array_key_exists('class-cache', $this->args['options'])) {
            $filePath = Extra::$pathStorageCache . '/' . $this->args['options']['class-cache'];
            if (is_file($filePath)) {
                $cache = $this->args['options']['class-cache'];
            }
        }

        $processId = exec(sprintf(
            "php extra run thread --class-name='%s' %s > %s 2>&1 & echo $!",
            $class,
            ($cache ? "--class-cache='{$cache}'" : ''),
            "/dev/null"
        ));
        self::printMessage("$class started in background!", 32);
        self::printMessage("PID: " . $processId, 32);
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Run Help", $cl);

        self::printLabel("extra run [args...] -[flags...] --[options...]", $cl);
        self::printMessage("args - command", $cl);
        self::print("serve - starting the server (default address '" . self::HOST . ':' . self::PORT . "')", $cl);
        self::print("script - run a custom command (specify the script name)", $cl);
        self::print("thread - run the 'Thread' task in the foreground (to run in the background use -d)", $cl);

        // serve
        self::printLabel("serve", $cl);
        self::printMessage("options - selection for action", $cl);
        self::print("host - hostname (default " . self::HOST . ")", $cl);
        self::print("port - port (default " . self::PORT . ")", $cl);
        self::printLabel("serve", $cl);

        // thread
        self::printLabel("thread", $cl);
        self::printMessage("flags - additional args for running", $cl);
        self::print("d - start process in background", $cl);
        self::printMessage("options - data for running", $cl);
        self::print("class-name - class name, with namespaces(example 'Jobs\ExampleJob')", $cl);
        self::print("class-cache - name cache file used in process (serializable)", $cl);
        self::printLabel("thread", $cl);

        self::printTitle("Run Help", $cl);
    }
}
