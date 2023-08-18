<?php

use Console\Core;

class __Cfg
{
    private $argument;

    function __construct($value = null)
    {
        $this->argument = $value;
        $this->handle();
    }

    public function handle(): void
    {
        if (!is_null($this->argument)) $this->resolution();
        else $this->help();
    }

    private function resolution(): void
    {
        if     ($this->argument == "apache")  $this->apache();
        elseif ($this->argument == "nginx")   $this->nginx();
        elseif ($this->argument == "ssl")     $this->ssl();
        else Core::logMessage("Команды '{$this->argument}' не существует!", 31);
    }

    private function apache(): void
    {
        $hosts = $hostsSSL = '';
        if (Warframe::$env['SSL_MODE_ON'] == 1)
            $file = dirname(__DIR__) . '/Template/Server/apache-ssl';
        else
            $file = dirname(__DIR__) . '/Template/Server/apache';

        foreach (Warframe::$env['HOSTS'] as $host) $hosts .= $host . ':' . Warframe::$env['APACHE_SERVER_PORT']. ' ';
        $template = str_replace("__HOSTS__", trim($hosts), file_get_contents($file));
        $template = str_replace("__ADMIN__", Warframe::$env['APACHE_SERVER_ADMIN'], $template);
        $template = str_replace("__ALIAS__", Warframe::$env['APACHE_SERVER_ALIAS'], $template);
        $template = str_replace("__NAME__", Warframe::$env['APACHE_SERVER_NAME'], $template);
        $template = str_replace("__ROOT__", PATH_PUBLIC . '/', $template);
        $template = str_replace("__DIR__", PATH_ROOT . '/', $template);
        $template = str_replace("__PHP_FPM__", Warframe::$env['PHP_FPM_PATH_SOCK'], $template);

        if (Warframe::$env['SSL_MODE_ON'] == 1) {
            foreach (Warframe::$env['HOSTS'] as $host) $hostsSSL .= $host . ':443 ';
            $template = str_replace("__HOSTS_SSL__", trim($hostsSSL), $template);
            $template = str_replace("__REDIRECT_TYPE__", (Warframe::$env['SSL_REDIRECT_BODY_DATA'] == 1) ? 307 : 301, $template);
            $template = str_replace("__CERTIFICATE_FILE__", Warframe::$env['SSL_CERTIFICATE_FILE'], $template);
            $template = str_replace("__CERTIFICATE_KEY_FILE__", Warframe::$env['SSL_CERTIFICATE_KEY_FILE'], $template);
        }

        $fp = fopen(PATH_APP . '/apache.conf', "w");
        fwrite($fp, $template);
        fclose($fp);
        Core::logMessage("Apache конфигурация сгенерирована успешно!", 32);
    }

    private function nginx(): void
    {
        if (Warframe::$env['SSL_MODE_ON'] == 1)
            $file = dirname(__DIR__) . '/Template/Server/nginx-ssl';
        else
            $file = dirname(__DIR__) . '/Template/Server/nginx';

        $hosts = implode(' ', Warframe::$env['HOSTS']);
        $template = str_replace("__HOSTS__", trim($hosts), file_get_contents($file));
        $template = str_replace("__PHP_FPM__", Warframe::$env['PHP_FPM_PATH_SOCK'], $template);
        $template = str_replace("__PORT__", Warframe::$env['NGINX_SERVER_PORT'], $template);
        $template = str_replace("__ROOT__", PATH_PUBLIC . '/', $template);
        if (Warframe::$env['SSL_MODE_ON'] == 1) {
            $template = str_replace("__REDIRECT_TYPE__", (Warframe::$env['SSL_REDIRECT_BODY_DATA'] == 1) ? 307 : 301, $template);
            $template = str_replace("__CERTIFICATE_FILE__", Warframe::$env['SSL_CERTIFICATE_FILE'], $template);
            $template = str_replace("__CERTIFICATE_KEY_FILE__", Warframe::$env['SSL_CERTIFICATE_KEY_FILE'], $template);
        }

        if (Warframe::$env['NGINX_SECURITY']) {
            $fileSecurity = dirname(__DIR__) . '/Template/Server/nginx-security';
            $templateSecurity = str_replace("__ACCESS_METHOD__", str_replace(',', '|', Warframe::$env['NGINX_SECURITY_ACCESS_METHOD']), file_get_contents($fileSecurity));
            $template = str_replace("__SECURITY__", $templateSecurity, $template);
        } else $template = str_replace("__SECURITY__", '', $template);

        if (Warframe::$env['NGINX_STATIC_LOCKER']) {
            $fileStatic = dirname(__DIR__) . '/Template/Server/nginx-static';
            $templateStatic = str_replace("__HOSTS__", trim($hosts), file_get_contents($fileStatic));
            $template = str_replace("__STATIC__", $templateStatic, $template);
        } else $template = str_replace("__STATIC__", '', $template);

        $fp = fopen(PATH_APP . '/nginx.conf', "w");
        fwrite($fp, $template);
        fclose($fp);
        Core::logMessage("Nginx конфигурация сгенерирована успешно!", 32);
    }

    private function ssl(): void
    {
        if (Warframe::$env['SSL_MODE_ON']) {

            if (!is_dir('ssl')) mkdir('ssl');
            exec("openssl genrsa -des3 -out ssl/server.key 1024;
                openssl req -new -key ssl/server.key -out ssl/server.csr;
                openssl rsa -in ssl/server.key -out ssl/server.key;
                openssl x509 -req -days 365 -in ssl/server.csr -signkey ssl/server.key -out ssl/server.crt");
            Core::logMessage("SSL конфигурация сгенерирована успешно!", 32);

        } else Core::logMessage("SSL модуль выключен.");
    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":apache       -  Создать конфигурационный файл (Apache).");
        Core::logText(":nginx        -  Создать конфигурационный файл (Nginx).");
        Core::logText(":ssl          -  Создать конфигурационный файл (SSL).");
        Core::logLabel("End");
    }

}
