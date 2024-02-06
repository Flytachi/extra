<?php

use Console\Core;
use Extra\Src\CDO;

class __Db
{
    private mixed $argument;
    private mixed $name;
    private String $path_database = PATH_APP . "/dist/";
    private String $path_data_seed = PATH_APP . "/dist/data";
    private String $path_cdo = "/Src/CDO.class.php";
    private String $seed_format = "json";

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
            if($this->argument == "skeleton") $this->skeleton();
            elseif($this->argument == "migrate") $this->migrate();
            elseif($this->argument == "compare") $this->compare();
            elseif($this->argument == "delete") $this->delete();
            elseif($this->argument == "seed") $this->seed();
            else Core::logMessage("Команды '{$this->argument}' не существует!", 31);
        } catch (\Error $e) {
            Core::logMessage("Ошибка в скрипте.", 31);
        }
    }

    private function migrate(): void
    {
        require dirname(__DIR__, 2) . $this->path_cdo;
        $db = new CDO(Warframe::$cfg['DATABASE'], Warframe::$cfg['GLOBAL_SETTING']['DEBUG']);

        // prepare
        if ($this->name) {
            $file = $this->path_database . $this->name . '.sql';
            if (!file_exists($file)) {
                Core::logMessage("Образ '{$this->name}' не найден.");
                return;
            }
        } else {
            $bases = glob($this->path_database . "sn#*.sql");
            if (count($bases) == 0) {
                Core::logMessage("Образы не найдены.");
                return;
            }
            $file = end($bases);
        }

        try {
            $db->exec(file_get_contents($file));
            Core::logMessage("Миграция прошла успешно.", 32);
            Core::logMessage("* skeleton: " . basename($file, '.sql'), 32);
        } catch (\Exception) {
            Core::logMessage("Во время миграции произошла ошибка.");
        }
    }

    private function skeleton(): void
    {
        if (!is_dir($this->path_database)) mkdir($this->path_database);
        $fileName = $this->path_database . ($this->name ?? ('sn#' . date("Y-m-d_H-i-s"))) . '.sql';
        $user = Warframe::$cfg['DATABASE']['USER'];
        $pass = Warframe::$cfg['DATABASE']['PASS'];
        $host = Warframe::$cfg['DATABASE']['HOST'];
        $port = Warframe::$cfg['DATABASE']['PORT'];
        $name = Warframe::$cfg['DATABASE']['NAME'];
        $this->mysqldump($user, $pass, $host, $port, $name, $fileName);
        Core::logMessage("Скелет базы успешно создан.", 32);
        Core::logMessage("* skeleton: '" . basename($fileName, '.sql') . "'", 32);
    }

    private function compare(): void
    {
        // prepare
        if ($this->name) {
            $file = $this->path_database . $this->name . '.sql';
            if (!file_exists($file)) {
                Core::logMessage("Образ '{$this->name}' не найден.");
                return;
            }
        } else {
            $bases = glob($this->path_database . "sn#*.sql");
            if (count($bases) == 0) {
                Core::logMessage("Образы не найдены.");
                return;
            }
            $file = end($bases);
        }
        $status = true;
        $currendData = $skeletonData = [];
        $user = Warframe::$cfg['DATABASE']['USER'];
        $pass = Warframe::$cfg['DATABASE']['PASS'];
        $host = Warframe::$cfg['DATABASE']['HOST'];
        $port = Warframe::$cfg['DATABASE']['PORT'];
        $name = Warframe::$cfg['DATABASE']['NAME'];

        // Skeleton/Current data
        $skeletonData = $this->sqlDataToArray(file_get_contents($file));
        $currendData = $this->sqlDataToArray($this->mysqldump($user, $pass, $host, $port, $name));


        $diffData = [
            ...array_diff_key($skeletonData, $currendData),
            ...array_diff_key($currendData, $skeletonData)
        ];

        Core::logMessage("* сравнение с (skeleton): '" . basename($file, '.sql') . "'", 32);

        if (count($diffData) > 0) {
            $status = false;
            foreach (array_keys($diffData) as $table) {
                Core::logTitle("============== ВНИМАНИЕ! ==============", 31);
                Core::logTitle("Найдена несанкционированная база данных", 31);
                Core::logLabel("Skeleton '$table'", 32);
                Core::logTextSplit((array_key_exists($table, $skeletonData)) ? $skeletonData[$table] : '');
                Core::logLabel("Current '$table'", 32);
                Core::logTextSplit((array_key_exists($table, $currendData)) ? $currendData[$table] : '');
                Core::logTitle("=======================================", 31);
            }
        }

        foreach ($skeletonData as $table => $slnSql) {
            if (array_key_exists($table, $currendData)) {
                $curSql = $currendData[$table];
                $static = strcmp($slnSql, $curSql);
                if ($static !== 0) {
                    $status = false;
                    Core::logTitle("============== ВНИМАНИЕ! ==============", 31);
                    Core::logTitle("========= Найдено расхождение =========", 31);
                    Core::logLabel("Skeleton '$table'", 32);
                    Core::logTextSplit((array_key_exists($table, $skeletonData)) ? $skeletonData[$table] : '');
                    Core::logLabel("Current '$table'", 32);
                    Core::logTextSplit((array_key_exists($table, $currendData)) ? $currendData[$table] : '');
                    Core::logTitle("=======================================", 31);
                }
            }
        }

        if ($status === true) Core::logMessage("Текущая база данных актуальна.", 32);
    }

    private function seed(): void
    {
        require dirname(__DIR__, 2) . $this->path_cdo;
        $db = new CDO(Warframe::$cfg['DATABASE'], Warframe::$cfg['GLOBAL_SETTING']['DEBUG']);

        if ($this->name) {

            if(!file_exists("$this->path_data_seed/$this->name.$this->seed_format")) {
                Core::logMessage("Ошибка не найдены данные.", 31);
                return;
            }

            $data = json_decode(file_get_contents("$this->path_data_seed/$this->name.$this->seed_format"), true);
            $i = 0;
            foreach ($data as $row) {
                $db->insert($this->name, $row);
                $i++;
            }
            Core::logMessage("Таблица {$this->name} ($i).", 32);

        } else{

            foreach (glob("$this->path_data_seed/*.$this->seed_format") as $file_name) {
                $table = pathinfo($file_name)['filename'];
                $data = json_decode(file_get_contents($file_name), true);
                $i = 0;
                foreach ($data as $row) {
                    $db->insert($table, $row);
                    $i++;
                }
                Core::logMessage("Таблица $table ($i).", 32);
            }

        }

        Core::logMessage("Данные успешно внесены.", 32);
    }

    private function delete(): void
    {
        require dirname(__DIR__, 2) . $this->path_cdo;
        $db = new CDO(Warframe::$cfg['DATABASE'], Warframe::$cfg['GLOBAL_SETTING']['DEBUG']);

        if ($this->name) {
            if ($db->query("SHOW TABLES LIKE '{$this->name}';")->rowCount()) {
                $sql = "SET FOREIGN_KEY_CHECKS = 0;\nDROP TABLE `{$this->name}`;SET FOREIGN_KEY_CHECKS = 1;";
                if (is_numeric($db->exec($sql))) Core::logMessage("Таблица '{$this->name}' успешно удалена.", 32);
                else Core::logMessage("Не удалось удалить таблицу '{$this->name}'.");
            }
            else Core::logMessage("Таблица {$this->name} не найдена.");
        } else {
            $sql = "SET FOREIGN_KEY_CHECKS = 0;\nDROP TABLE ";
            $tables = '';
            foreach ($db->query("SHOW TABlES") as $table) $tables .= "`". $table['Tables_in_'.Warframe::$cfg['DATABASE']['NAME']] . "`,";

            if ($tables != '') {
                $sql = $sql . rtrim($tables, ',') . ";\nSET FOREIGN_KEY_CHECKS = 1;";
                if (is_numeric($db->exec($sql))) Core::logMessage("База данных успешно удалена.", 32);
                else Core::logMessage("Не удалось удалить базу данных.");
            } else Core::logMessage("В базе данных не найдено ни одной таблицы.");
        }
    }

    private function sqlDataToArray(string $data): array
    {
        $resultData = [];
        foreach (explode("-- Table structure for table", $data) as $item) {
            $item = explode("CREATE ", $item);
            if (array_key_exists(1, $item)) {
                $name = explode('`', explode('TABLE IF NOT EXISTS `', $item[1])[1])[0];
                $item = explode(";", $item[1])[0];
                $resultData[$name] = 'CREATE ' . $item . ';';
            }
        }
        return $resultData;
    }

    private function mysqldump(string $user, string $pass, string $host, string $port, string $name, string $fileName = null): string|false|null
    {
        return shell_exec(
            "mysqldump -u'$user' -p'$pass' -h'$host' --protocol=TCP -P'$port' " .
            "--skip-opt --single-transaction --tz-utc --no-data --create-options --triggers $name " .
            "| sed 's/^CREATE TABLE /CREATE TABLE IF NOT EXISTS /' " .
            "| sed 's/ AUTO_INCREMENT=[0-9]*\b//' " .
            ((is_null($fileName)) ? "| cat" : "> {$fileName}")
        );

    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":migrate      -  Миграция образа базы данных (можно указать имя образа).");
        Core::logText(":skeleton     -  Создать образ базы данных (можно указать имя образа).");
        Core::logText(":compare      -  Сравнение образа и текущей базы данных (можно указать имя образа).");
        Core::logText(":seed         -  Внести данные в базу данных (можно указать таблицу).");
        Core::logText(":delete       -  Удалить все таблице в базе данных (можно указать таблицу).");
        Core::logLabel("End");
    }

}
