<?php

namespace Extra\Console\Command;

use Extra\Console\Inc\Cmd;

class Run extends Cmd
{
    public static string $title = "command runnable control";
    const HOST = 'localhost';
    const PORT = 8000;

    public function handle(): void
    {
        self::printTitle("Run", 32);

        if (
            count($this->args['arguments']) > 1
        ) $this->resolution();
        else {
            self::printMessage("Enter argument");
            self::print("Example: extra run serve");
        }

        self::printTitle("Run", 32);
    }

    private function resolution(): void
    {
        if (array_key_exists(1, $this->args['arguments'])) {
            switch ($this->args['arguments'][1]) {
                case 'serve': $this->serveArg(); break;
                case 'script': $this->scriptArg(); break;
                default:
                    self::printMessage("Argument '{$this->args['arguments'][1]}' not found");
                    break;
            }
        }
    }

    private function serveArg(): void
    {
        $host = (array_key_exists('host', $this->args['options'])) ? $this->args['options']['host'] :self::HOST;
        $port = (array_key_exists('port', $this->args['options'])) ? $this->args['options']['port'] :self::PORT;
        $connection = @fsockopen($host, $port);

        if (is_resource($connection)) {
            self::printMessage("Permission denied, 'http://{$host}:{$port}' is already busy!");
            fclose($connection);
        } else {
            self::printMessage("Starting the server to 'http://" . $host . ':' . $port . "'", 32);
            exec("php -S {$host}:{$port} -t public/");
        }
    }

    private function scriptArg(): void
    {
        self::printMessage("Command 'script' is in development");
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Run Help", $cl);

        self::printLabel("extra run [args...] -[flags...] --[options...]", $cl);
        self::printMessage("args - command", $cl);
        self::print("serve - starting the server (default address '" . self::HOST . ':' . self::PORT . "')", $cl);
        self::print("script - run the prepared script specified in 'Config/constants.php' (specify the name of the script)", $cl);

        // serve
        self::printLabel("serve", $cl);
        // self::printMessage("flags - selection addition for running", $cl);
        // self::print("d - start to background", $cl);
        self::printMessage("options - selection for action", $cl);
        self::print("host - hostname (default " . self::HOST . ")", $cl);
        self::print("port - port (default " . self::PORT . ")", $cl);
        self::printLabel("serve", $cl);

        self::printTitle("Run Help", $cl);
    }

}