<?php

class __Seed
{
    protected String $name;
    private String $path = "dist/data";
    private String $path_connection = "/Src/Connection/__load__.php";
    private String $format = "json"; 
    private Array $json = array();

    function __construct(string $name = null)
    {
        if ($name) {
            $this->name = $name;
            if ($this->generate()) {
                echo "\033[32m". " Генерация прошла успешно.\n";
            }else {
                echo "\033[31m"." Ошибка при генерации.\n";
            }
        } else {
            echo "\033[33m"." Введите аргумент! (название таблицы)\n";
            return 0;
        }
        
    }

    private function generate(): bool
    {
        require dirname(__DIR__, 2) . $this->path_connection;
        $ini = cfgGet();
        $db = (new Connect($ini['DATABASE']))->connection($ini['GLOBAL_SETTING']['DEBUG']);

        if ($db->query("SHOW TABLES LIKE '$this->name';")->rowCount()) {
            foreach ($db->query("SELECT * FROM $this->name") as $value) {
                $this->json[] = $value;
            }
            return $this->create_file();
        } else {
            echo "\033[31m"." Таблица $this->name не найдена.\n";
            return false;
        }
    }

    private function create_file(): bool
    {
        $file = fopen(APP_FOLDER . "/$this->path/$this->name.$this->format", "w");
        fwrite($file, json_encode($this->json, JSON_PRETTY_PRINT));
        return fclose($file);
    }
}

?>