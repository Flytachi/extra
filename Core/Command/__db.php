<?php

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
            if($this->argument == "migrate") $this->migrate();
            elseif($this->argument == "delete") $this->delete();
            elseif($this->argument == "seed") $this->seed();
            else echo "\033[31m"." Нет такого аргумента.\n";
        } catch (\Error $e) {
            echo $e->getMessage();
//            echo "\033[31m"." Ошибка в скрипте.\n";
        }
    }

    private function migrate(): void
    {
        require dirname(__DIR__, 2) . $this->path_cdo;
        $ini = cfgGet();
        $db = new CDO($ini['DATABASE'], $ini['GLOBAL_SETTING']['DEBUG']);
        
        if ($this->name) {
            try {
                $file = $this->path_database . $this->name . '.sql';
                if (file_exists($file)) {
                    $db->exec(file_get_contents($file));
                    echo "\033[32m" . " Миграция прошла успешно.\n";
                } else echo "\033[31m"." Файл не найден.\n";
            } catch (\Exception) {
                echo "\033[31m" . " Во время миграции произошла ошибка.\n";
            }
        } else {
            echo "\033[31m"." Не выбран файл.\n";
        }
    }

    private function delete(): void
    {
        require dirname(__DIR__, 2) . $this->path_cdo;
        $ini = cfgGet();
        $db = new CDO($ini['DATABASE'], $ini['GLOBAL_SETTING']['DEBUG']);
        $sql = "SET FOREIGN_KEY_CHECKS = 0;\nDROP TABLE ";
        foreach ($db->query("SHOW TABlES") as $table) $sql .= "`". $table['Tables_in_'.$ini['DATABASE']['NAME']] . "`,";
        $sql = rtrim($sql, ',') . ";\nSET FOREIGN_KEY_CHECKS = 1;";
        $db->exec($sql);
        echo "\033[32m"." База данных успешно удалена.\n";
    }

    private function seed(): void
    {
        require dirname(__DIR__, 2) . $this->path_cdo;
        $ini = cfgGet();
        $db = new CDO($ini['DATABASE'], $ini['GLOBAL_SETTING']['DEBUG']);

        if (isset($this->name)) {

            if(!file_exists("$this->path_data_seed/$this->name.$this->seed_format")){
                echo "\033[31m"." Ошибка не найдены данные.\n";
                return;
            }

            $data = json_decode(file_get_contents("$this->path_data_seed/$this->name.$this->seed_format"), true);
            foreach ($data as $row) {
                $db->insert($this->name, $row);
            }

        }else{

            foreach (glob("$this->path_data_seed/*.$this->seed_format") as $file_name) {
                $table = pathinfo($file_name)['filename'];
                $data = json_decode(file_get_contents($file_name), true);
                $i = 0;
                foreach ($data as $row) {
                    $i++;
                    $db->insert($table, (object) $row);
                }
                echo "\033[32m"." Таблица $table ($i).\n";
            }

        }

        echo "\033[32m"." Данные успешно внесены.\n";
    }

    private function create_file($code): void
    {
        $file_name = date("Y-m-d_h-i-s");
        $file = fopen("$this->path_base/$file_name.$this->format", "w");
        fwrite($file, $code);
    }

    private function help(): void
    {
        echo "\033[33m"." =======> Help <======= \n";
        echo "\033[33m"."  :migrate  -  Миграция образа базы данных.\n";
        echo "\033[33m"."  :delete   -  Удалить все таблице в базе данных.\n";
        echo "\033[33m"."  :seed     -  Внести данные в базу данных. (можно указать таблицу)\n";
        echo "\033[33m"." =======> Help <======= \n";
    }

}
