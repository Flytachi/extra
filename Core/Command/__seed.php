<?php

use Console\Core;
use Extra\Src\CDO;

class __Seed
{
    protected String $name;
    private String $path_data_seed = PATH_APP . "/dist/data";
    private String $path_cdo = "/Src/CDO.class.php";
    private String $format = "json"; 
    private Array $json = array();

    function __construct(string $name = null)
    {
        Warframe::coreLoader();
        if ($name) {
            $this->name = $name;
            if ($this->generate()) Core::logMessage("Генерация прошла успешно.", 32);
            else Core::logMessage("Ошибка при генерации.", 31);
        } else Core::logMessage("Введите аргумент! (название таблицы).");
    }

    private function generate(): bool
    {
        require dirname(__DIR__, 2) . $this->path_cdo;
        $db = new CDO(Warframe::$cfg['DATABASE'], Warframe::$cfg['GLOBAL_SETTING']['DEBUG']);

        if ($db->query("SHOW TABLES LIKE '$this->name';")->rowCount()) {
            foreach ($db->query("SELECT * FROM $this->name") as $value)
                $this->json[] = $value;
            return $this->create_file();
        } 
        else Core::logMessage("Таблица {$this->name} не найдена.");
        return false;
    }

    private function create_file(): bool
    {
        $file = fopen("$this->path_data_seed/$this->name.$this->format", "w");
        fwrite($file, json_encode($this->json, JSON_PRETTY_PRINT));
        return fclose($file);
    }
}
