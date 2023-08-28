<?php

use Console\Core;

class __Socket
{
    private mixed $argument;
    private mixed $name;
    private string $PIDStorage = 'pid';

    function __construct($value = null, $name = null)
    {
        $this->argument = $value;
        $this->name = $name;
        $this->handle();
    }

    private function handle(): void
    {
        if (!is_null($this->argument)) $this->resolution();
        else $this->help();
    }

    private function resolution(): void
    {
        try {
            if ($this->argument == "run") $this->run();
            elseif ($this->argument == "start") $this->start();
            elseif ($this->argument == "stop") $this->stop();
            elseif ($this->argument == "status") $this->status();
            else Core::logMessage("Команды '{$this->argument}' не существует!", 31);
        } catch (Error $e) {
            Core::logMessage("Ошибка в скрипте.", 31);
        }
    }

    private function start(): void
    {
        if ($this->name) {
            $socketFile = PATH_APP . '/Sockets/' . $this->name . '.php';

            if (file_exists($socketFile)) {

                $selfPID = $this->jsonPIDdata($this->name);
                if ($selfPID) {
                    Core::logMessage("Сокет уже запущен!");
                    Core::logMessage("PID: " . $selfPID);
                } else {
                    $processId = shell_exec(sprintf(
                        '%s > %s 2>&1 & echo $!',
                        "php -q box socket:run " . str_replace('\\', '\\\\', $this->name),
                        "/dev/null"
                    ));
                    $this->jsonAddPID($processId, $this->name);
                    Core::logMessage("Сокет {$this->name} запущен!", 32);
                    Core::logMessage("PID: " . $processId, 32);
                }

            } else Core::logMessage("Сокет не найден!");

        } else Core::logMessage("Укажите имя сокета!");
    }

    private function stop(): void
    {
        if ($this->name) {

            $socketFile = PATH_APP . '/Sockets/' . $this->name . '.php';
            
            if (file_exists($socketFile)) {
                
                $selfPID = $this->jsonPIDdata($this->name);
                if ($selfPID) {
                    if (posix_kill($selfPID, SIGKILL)) {
                        $this->jsonDeletePID($selfPID);
                        Core::logMessage("Сокет {$this->name} остановлен.", 32);
                    }
                } else Core::logMessage("Не найден сокет процесс!");

            } else Core::logMessage("Сокет не найден!");
        } else Core::logMessage("Укажите имя сокета!");
    }

    private function run(): void
    {
        if ($this->name)
            $this->runSocket($this->name, PATH_APP . '/Sockets/' . $this->name . '.php');
        else Core::logMessage("Укажите имя сокета!");
    }

    private function status(): void
    {
        $appSockets = glob(PATH_APP . '/Sockets/*.php');

        foreach ($appSockets as $socketFile) {
            $class = basename($socketFile, '.php');
            $socket = new ("\\Sockets\\" . $class);
            $status = ($socket->statusConnection() ? "\033[34mACTIVE" : "\033[30mPASSIVE");
            Core::logMessage(
                $class . "\t "
                . $socket->getIp() . ':' . $socket->getPort()
                . "\t\t " . $status
            );
        }
    }

    private function runSocket(string $name, string $path): void
    {
        if (file_exists($path)) {

            $socket = new ("\\Sockets\\" . $name);
            if ($socket->statusConnection()) Core::logMessage("Сокет уже запущен!");
            else {
                Core::logMessage("Сокет {$name} запущен!", 32);
                $socket->start();
            }

        } else Core::logMessage("Сокет не найден!");
    }

    private function jsonAddPID(int $pid, string $pidName): void
    {
        $filePath = PATH_APP . '/' . $this->PIDStorage . '.json';
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), 1);
            $data['sockets'][$pid] = $pidName;
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents($filePath, $jsonData);
        } else {
            $file = fopen($filePath, "x");
            $data = ['sockets' => [$pid=>$pidName]];
            fwrite($file, json_encode($data, JSON_PRETTY_PRINT));
            chmod($filePath, 0777);
        }
    }

    private function jsonDeletePID(int $pid): void
    {
        $filePath = PATH_APP . '/' . $this->PIDStorage . '.json';
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), 1);
            unset($data['sockets'][$pid]);
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents($filePath, $jsonData);
        }
    }

    private function jsonPIDData(string $pidName): false|int|string
    {
        $filePath = PATH_APP . '/' . $this->PIDStorage . '.json';
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), 1);
            return array_search($pidName, $data['sockets']);
        } else return false;
    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":run          -  Запустить сокет сервер.");
        Core::logText(":start        -  Запустить сокет сервер в фоновом процессе.");
        Core::logText(":stop         -  Остановить сокет сервер.");
        Core::logText(":status       -  Статус сокетов.");
        Core::logLabel("End");
    }

}
