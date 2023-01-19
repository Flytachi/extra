<?php

use Console\Core;

class __Socket
{
    private mixed $argument;
    private mixed $name;
    private string $PIDstorage = 'pid';

    function __construct($value = null, $name = null)
    {
        Warframe::coreLoader();
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
            dd($e);
            Core::logMessage("Ошибка в скрипте.", 31);
        }
    }

    private function start(): void
    {
        if ($this->name) {

            $names = explode('\\', $this->name);
            if (ROUTE_PLUGIN_SYSTEM &&  count($names) != 1) 
                $socketFile = PATH_PLUGIN . '/Frame.' . $names[0] . '/sockets/' . $names[1] . '.php';
            else $socketFile = PATH_APP . '/sockets/' . $this->name . '.php';

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

            $names = explode('\\', $this->name);
            if (ROUTE_PLUGIN_SYSTEM &&  count($names) != 1) 
                $socketFile = PATH_PLUGIN . '/Frame.' . $names[0] . '/sockets/' . $names[1] . '.php';
            else $socketFile = PATH_APP . '/sockets/' . $this->name . '.php';
            
            if (file_exists($socketFile)) {
                
                $selfPID = $this->jsonPIDdata($this->name);
                if ($selfPID != false) {
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
        if ($this->name) {

            if (ROUTE_PLUGIN_SYSTEM) {
                $names = explode('\\', $this->name); 
                if (count($names) == 1) $this->runSocket($names[0], PATH_APP . '/sockets/' . $this->name . '.php');
                else $this->runSocket($this->name, PATH_PLUGIN . '/Frame.' . $names[0] . '/sockets/' . $names[1] . '.php');
            } else $this->runSocket($this->name, PATH_APP . '/sockets/' . $this->name . '.php');
            
        } else Core::logMessage("Укажите имя сокета!");
    }

    private function status(): void
    {
        $appSockets = glob(PATH_APP . '/sockets/*.php');

        if (ROUTE_PLUGIN_SYSTEM) {
            $pluginSockets = glob(PATH_PLUGIN . '/Frame.*/sockets/*.php');
            if((count($appSockets) + count($pluginSockets)) == 0) {
                Core::logMessage("Не найдено ни одного сокета!");
                return;
            }

            foreach ($pluginSockets as $socketFile) {
                include $socketFile;
                $plugin = str_replace('Frame.','', basename(dirname($socketFile, 2)));
                $class = basename($socketFile, '.php');
                $socket = new ($plugin . '\\' . $class);
                $status = ($socket->statusConnection() ? "\033[34mACTIVE" : "\033[30mPASSIVE");
                Core::logMessage(
                    $plugin . '\\' . $class . "\t "
                    . $socket->getIp() . ':' . $socket->getPort()
                    . "\t\t " . $status
                );
            }
        } else {
            if(count($appSockets) == 0) {
                Core::logMessage("Не найдено ни одного сокета!");
                return;
            }
        }

        foreach ($appSockets as $socketFile) {
            include $socketFile;
            $class = basename($socketFile, '.php');
            $socket = new $class;
            $status = ($socket->statusConnection() ? "\033[34mACTIVE" : "\033[30mPASSIVE");
            Core::logMessage(
                $class . "\t "
                . $socket->getIp() . ':' . $socket->getPort()
                . "\t\t " . $status
            );
        }
    }

    private function runSocket(string $name, string $path)
    {
        if (file_exists($path)) {
            
            include $path;
            $socket = new $name;
            if ($socket->statusConnection()) Core::logMessage("Сокет уже запущен!");
            else {
                Core::logMessage("Сокет {$name} запущен!", 32);
                $socket->start();
            }

        } else Core::logMessage("Сокет не найден!");
    }

    private function jsonAddPID(int $pid, string $pidName)
    {
        $filePath = PATH_APP . '/' . $this->PIDstorage . '.json';
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), 1);
            $data['sockets'][$pid] = $pidName;
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents($filePath, $jsonData);
        } else {
            $file = fopen($filePath, "x");
            $data = ['sockets' => [$pid=>$pidName]];
            fwrite($file, json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    private function jsonDeletePID(int $pid)
    {
        $filePath = PATH_APP . '/' . $this->PIDstorage . '.json';
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), 1);
            unset($data['sockets'][$pid]);
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents($filePath, $jsonData);
        }
    }

    private function jsonPIDdata(string $pidName)
    {
        $filePath = PATH_APP . '/' . $this->PIDstorage . '.json';
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
