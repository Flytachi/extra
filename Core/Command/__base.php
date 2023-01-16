<?php

use Console\Core;

class __Base
{
    private String $db_driver = "mysql";
    private String $db_host = "localhost";
    private String $db_charset = "utf8";
    private String $db_user = "root";
    private String $db_password = "root";
    private string $db_login;

    function __construct(string $db_login = null, string $db_password = null)
    {
        if ( isset($db_login) and isset($db_password) ) {
            $this->db_login = $db_login;
            $this->db_password = $db_password;
            $this->create();
        }else $this->handle();
    }

    private function handle(): void
    {
        Core::logLabel("Help");
        Core::logText("Требуется 2 аргумента \"base:!1 !2\"");
        Core::logText("!1 => Логин пользователя от базы данных.");
        Core::logText("!2 => Пароль пользователя от базы данных.");
        Core::logLabel("End");
    }

    private function create(): void
    {
        $create_db_name = Warframe::$cfg['DATABASE']['NAME'];
        $create_db_user = Warframe::$cfg['DATABASE']['USER'];
        $create_db_port = Warframe::$cfg['DATABASE']['PORT'];
        $create_db_password = Warframe::$cfg['DATABASE']['PASS'];
        $DNS = "$this->db_driver:host=$this->db_host;port=$create_db_port;charset=$this->db_charset";
        
        // Site Constants
        try {
            $rootDB = new PDO($DNS, $this->db_user, $this->db_password);
            $rootDB->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $rootDB->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $rootDB->SetAttribute(PDO::ATTR_EMULATE_PREPARES, False);
            $rootDB->exec("CREATE USER IF NOT EXISTS '$create_db_user'@'%' IDENTIFIED BY '$create_db_password';");
            $rootDB->exec("GRANT USAGE ON *.* TO '$create_db_user'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;");
            $rootDB->exec("CREATE DATABASE IF NOT EXISTS `$create_db_name`;GRANT ALL PRIVILEGES ON `$create_db_name`.* TO '$create_db_user'@'%';");
            $rootDB->exec("FLUSH PRIVILEGES;");
            Core::logMessage("Пользователь и база данных успешно созданы.", 32);
        } catch (PDOException) {
            Core::logMessage("Ошибка в скрипте.", 31);
            // die($e);
        }
    }
}
