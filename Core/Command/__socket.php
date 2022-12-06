<?php

class __Socket
{
    private mixed $argument;
    private mixed $name;
    private string $PIDstorage = 'pid';

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
            else echo "\033[33m". " Команды '$this->argument' не существует!\n";
        } catch (Error $e) {
            dd($e);
            // echo "\033[31m"." Ошибка в скрипте.\n";
        }
    }

    private function start(): void
    {
        if ($this->name) {
            require dirname(__DIR__, 2) . '/warframe.php';
            $socketFile = PATH_APP . '/sockets/' . $this->name . '.php';
            if (file_exists($socketFile)) {

                $selfPID = $this->jsonPIDdata($this->name);
                if ($selfPID) {
                    echo "\033[33m". " Сокет уже запущен! \n PID: $selfPID\n";
                } else {
                    $processId = shell_exec(sprintf(
                        '%s > %s 2>&1 & echo $!',
                        "php -q box socket:run {$this->name}",
                        "/dev/null"
                    ));
                    $this->jsonAddPID($processId, $this->name);
                    echo "\033[32m" . " Сокет $this->name запущен!\n PID: " . $processId;
                }

            } else echo "\033[33m". " Сокет не найден!\n";
        } else echo "\033[33m". " Укажите имя сокета!\n";
    }

    private function stop(): void
    {
        if ($this->name) {
            require dirname(__DIR__, 2) . '/warframe.php';
            $socketFile = PATH_APP . '/sockets/' . $this->name . '.php';
            if (file_exists($socketFile)) {
                
                $selfPID = $this->jsonPIDdata($this->name);
                if ($selfPID != false) {
                    if (posix_kill($selfPID, SIGKILL)) {
                        $this->jsonDeletePID($selfPID);
                        echo "\033[32m" . " Сокет $this->name остановлен.\n";
                    }
                } else echo "\033[33m". " Не найден сокет процесс!\n";

            } else echo "\033[33m". " Сокет не найден!\n";
        } else echo "\033[33m". " Укажите имя сокета!\n";
    }

    private function run(): void
    {
        if ($this->name) {
            require dirname(__DIR__, 2) . '/warframe.php';
            $socketFile = PATH_APP . '/sockets/' . $this->name . '.php';
            if (file_exists($socketFile)) {
                
                Warframe::loadSrc();
                include $socketFile;
                $socket = new $this->name;
                if ($socket->statusConnection()) {
                    echo "\033[33m". " Сокет уже запущен!\n";
                } else $socket->start();

            } else echo "\033[33m". " Сокет не найден!\n";
        } else echo "\033[33m". " Укажите имя сокета!\n";
    }

    private function status(): void
    {
        $socketFolder = PATH_APP . '/sockets/';
        if (file_exists($socketFolder)) {
            require dirname(__DIR__, 2) . '/warframe.php';
            Warframe::loadSrc();

            foreach (glob($socketFolder. '/*.php') as $socketFile) {
                include $socketFile;
                $class = basename($socketFile, '.php');
                (new $class)->connection();
            }
            
        } else echo "\033[33m". " Папка сокетов не найдена!\n";
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
            $data = [
                'sockets' => [$pid=>$pidName]
            ];
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
        echo "\033[33m"." =======> Help <======= \n";
        echo "\033[33m"."  :run          -  Запустить сокет сервер.\n";
        echo "\033[33m"."  :start        -  Запустить сокет сервер в фоновом процессе.\n";
        echo "\033[33m"."  :stop         -  Остановить сокет сервер.\n";
        echo "\033[33m"."  :status       -  Статус сокетов.\n";
        echo "\033[33m"." =======> Help <======= \n";
    }

}
