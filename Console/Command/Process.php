<?php

namespace Extra\Console\Command;

use Extra\Console\Inc\Cmd;

class Process extends Cmd
{
    public static string $title = "command process control";

    public function handle(): void
    {
        self::printTitle("Process", 32);

        if (
            count($this->args['arguments']) > 1
        ) $this->resolution();
        else {
            self::printMessage("Enter argument");
            self::print("Example: extra process run");
        }

        self::printTitle("Process", 32);
    }

    private function resolution(): void
    {
        if (array_key_exists(1, $this->args['arguments'])) {
            switch ($this->args['arguments'][1]) {
                case 'run': $this->runArg(); break;
                default:
                    self::printMessage("Argument '{$this->args['arguments'][1]}' not found");
                    break;
            }
        }
    }

    private function runArg(): void
    {
        if (
            array_key_exists('class-name', $this->args['options'])
            && $this->args['options']['class-name']
        ) {
            $class = $this->args['options']['class-name'];
            if (class_exists($class)) {

                if (
                    array_key_exists(0, $this->args['flags'])
                    && $this->args['flags'][0] == 'd'
                ) $this->runnableToBack($class);
                else $this->runnable($class);

            } else self::printMessage("The specified class '{$class}' was not found");
        } else self::printMessage("class-name option not specified");
    }

    private function runnable(string $class): void
    {
        // Cache Data
        $data = null;
        if (array_key_exists('class-cache', $this->args['options'])) {
            $filePath = PATH_CACHE . '/' . $this->args['options']['class-cache'];
            if (is_file($filePath)) {
                $data = unserialize(file_get_contents($filePath));
                unlink($filePath);
            }
        }

        self::printMessage("{$class} start", 32);
        ($class)::start($data);
        self::printMessage("{$class} end", 32);
    }

    private function runnableToBack(string $class): void
    {
        // Cache
        $cache = null;
        if (array_key_exists('class-cache', $this->args['options'])) {
            $filePath = PATH_CACHE . '/' . $this->args['options']['class-cache'];
            if (is_file($filePath)) $cache = $this->args['options']['class-cache'];
        }

        $processId = exec(sprintf(
            "php -q extra process run --class-name='%s' %s > %s 2>&1 & echo $!",
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
        self::printTitle("Process Help", $cl);

        self::printLabel("extra process [args...] -[flags...] --[options...]", $cl);
        self::printMessage("args - command", $cl);
        self::print("run - process starting", $cl);
        self::printMessage("flags - additional args for running", $cl);
        self::print("d - start process in background", $cl);
        self::printMessage("options - data for running", $cl);
        self::print("class-name - class name, with namespaces(example 'Jobs\ExampleJob')", $cl);
        self::print("class-cache - name cache file used in process (serializable)", $cl);

        self::printTitle("Process Help", $cl);
    }

}