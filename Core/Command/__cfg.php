<?php

use Console\Core;

class __Cfg
{
    private $argument;
    private Array $default_configurations = [
        'HOSTS' => [],
        'APACHE' => [
            'SERVER_ADMIN' => 'webmaster@dummy-host.example.loc',
            'SERVER_ALIAS' => null,
            'SERVER_NAME' => null,
            'SERVER_PORT' => 80,
        ],
        'NGINX' => [
            'PHP_FPM_SOCK' => '/run/php-fpm/php-fpm.sock',
            'SERVER_PORT' => 80,
            'ACCESS_METHOD' => 'HEAD,OPTIONS,GET,POST,PUT,DELETE',
        ],
        'SSL' => [
            'MODE_ON' => null,
            'REDIRECT_BODY_DATA' => false,
            'CERTIFICATE_FILE' => null,
            'CERTIFICATE_KEY_FILE' => null,
        ],
        'SECURITY' => [
            'PRODUCT_GUARD' => null,
            'PRODUCT_HOST' => null,
            'PRODUCT_KEY' => null,
            'PRODUCT_FIRMWARE' => null
        ],
        'GLOBAL_SETTING' => [
            'TIME_ZONE' => 'UTC',
            'SESSION_TIMEOUT' => null,
            'SESSION_LIFE' => null,
            'DEBUG' => true,
        ],
        'DATABASE' => [
            'DRIVER' => 'mysql',
            'CHARSET' => 'utf8',
            'HOST' => 'localhost',
            'PORT' => 3306,
            'NAME' => null,
            'USER' => null,
            'PASS' => null,
        ],
        'TELEGRAM' => [
            'TOKEN' => null
        ]
    ];

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
        if ($this->argument == "init") $this->init();
        elseif ($this->argument == "gen") $this->generate();
        elseif ($this->argument == "edit") $this->edit();
        elseif ($this->argument == "show") $this->show();
        elseif ($this->argument == "apache") $this->apache();
        elseif ($this->argument == "nginx") $this->nginx();
        elseif ($this->argument == "ssl") $this->ssl();
        else Core::logMessage("Команды '{$this->argument}' не существует!", 31);
    }

    private function init(): void
    {
        if(file_exists(CFG_PATH_OPEN)) {
            Core::logMessage(basename(CFG_PATH_OPEN) . " уже существует!");
            return;
        }
        $root = dirname(__DIR__, 4) . '/';
        $this->default_configurations['APACHE']['SERVER_ALIAS'] = basename($root);
        $this->default_configurations['APACHE']['SERVER_NAME'] = basename($root);
        $this->default_configurations['SSL']['CERTIFICATE_FILE'] = dirname(__DIR__, 4) . '/ssl/server.crt';
        $this->default_configurations['SSL']['CERTIFICATE_KEY_FILE'] = dirname(__DIR__, 4) . '/ssl/server.key';

        
        $fp = fopen(CFG_PATH_OPEN, "x");
        fwrite($fp, $this->arrayToIni($this->default_configurations));
        fclose($fp);
        chmod(CFG_PATH_OPEN, 0777);
        Core::logMessage(basename(CFG_PATH_OPEN) . " сгенерирован успешно!", 32);
        
    }

    private function generate(): void
    {
        if (file_exists(CFG_PATH_OPEN)) {

            $sett = parse_ini_file(CFG_PATH_OPEN, true);
            if (!file_exists(CFG_PATH_CLOSE)) {

                rename(CFG_PATH_OPEN, CFG_PATH_CLOSE);
                $fp = fopen(CFG_PATH_CLOSE, "w+");
                fwrite($fp, chunk_split( bin2hex(zlib_encode(json_encode($sett), ZLIB_ENCODING_DEFLATE)) , 50, "\n") );
                fclose($fp);
                Core::logMessage(basename(CFG_PATH_CLOSE) . " сгенерирован успешно!", 32);

            } else Core::logMessage(basename(CFG_PATH_CLOSE) . " уже существует!");

        } else Core::logMessage(basename(CFG_PATH_OPEN) . " не найден!");
    }

    private function edit(): void
    {
        if (file_exists(CFG_PATH_CLOSE)) {

            $cfg = $this->arrayToIni(cfgGet());
            rename(CFG_PATH_CLOSE, CFG_PATH_OPEN);
            $fp = fopen(CFG_PATH_OPEN, "w+");
            fwrite($fp, $cfg);
            fclose($fp);
            Core::logMessage(basename(CFG_PATH_OPEN) . " сгенерирован успешно!", 32);

        } else Core::logMessage(basename(CFG_PATH_CLOSE) . " не найден!");
    }

    private function show(): void
    {
        if (file_exists(CFG_PATH_CLOSE)) {

            Core::logTitle("Конфигурации");
            foreach (cfgGet() as $label => $conf) {
                Core::logLabel($label);
                foreach ($conf as $key => $value) Core::logText($key . ' = ' . $value);
            }
            Core::logTitle("============");

        } else Core::logMessage(basename(CFG_PATH_CLOSE) . " не найден!");
    }

    private function apache(): void
    {
        if (file_exists(CFG_PATH_CLOSE)) {

            $ini = cfgGet();
            $hosts = $hostsSSL = '';
            if ($ini['SSL']['MODE_ON'] == 1)
                $file = dirname(__DIR__) . '/Template/Server/apache-ssl';
            else 
                $file = dirname(__DIR__) . '/Template/Server/apache';

            foreach ($ini['HOSTS'] as $host) $hosts .= $host . ':' . $ini['APACHE']['SERVER_PORT']. ' ';
            $template = str_replace("__HOSTS__", trim($hosts), file_get_contents($file));
            $template = str_replace("__ADMIN__", $ini['APACHE']['SERVER_ADMIN'], $template);
            $template = str_replace("__ALIAS__", $ini['APACHE']['SERVER_ALIAS'], $template);
            $template = str_replace("__NAME__", $ini['APACHE']['SERVER_NAME'], $template);
            $template = str_replace("__ROOT__", PATH_PUBLIC . '/', $template);
            $template = str_replace("__DIR__", PATH_ROOT . '/', $template);

            if ($ini['SSL']['MODE_ON'] == 1) {
                foreach ($ini['HOSTS'] as $host) $hostsSSL .= $host . ':443 ';
                $template = str_replace("__HOSTS_SSL__", trim($hostsSSL), $template);
                $template = str_replace("__REDIRECT_TYPE__", ($ini['SSL']['REDIRECT_BODY_DATA'] == 1) ? 307 : 301, $template);
                $template = str_replace("__CERTIFICATE_FILE__", $ini['SSL']['CERTIFICATE_FILE'], $template);
                $template = str_replace("__CERTIFICATE_KEY_FILE__", $ini['SSL']['CERTIFICATE_KEY_FILE'], $template);
            }
            
            $fp = fopen(PATH_APP . '/apache.conf', "w");
            fwrite($fp, $template);
            fclose($fp);
            Core::logMessage("Apache конфигурация сгенерирована успешно!", 32);

        } else Core::logMessage(basename(CFG_PATH_CLOSE) . " не найден!");
    }

    private function nginx(): void
    {
        if (file_exists(CFG_PATH_CLOSE)) {

            $ini = cfgGet();
            if ($ini['SSL']['MODE_ON'] == 1)
                $file = dirname(__DIR__) . '/Template/Server/nginx-ssl';
            else
                $file = dirname(__DIR__) . '/Template/Server/nginx';

            $hosts = implode(' ', $ini['HOSTS']);
            $template = str_replace("__HOSTS__", trim($hosts), file_get_contents($file));
            $template = str_replace("__PHP_FPM__", $ini['NGINX']['PHP_FPM_SOCK'], $template);
            $template = str_replace("__PORT__", $ini['NGINX']['SERVER_PORT'], $template);
            $template = str_replace("__ACCESS_METHOD__", str_replace(',', '|', $ini['NGINX']['ACCESS_METHOD']), $template);
            $template = str_replace("__ROOT__", PATH_PUBLIC . '/', $template);
            if ($ini['SSL']['MODE_ON'] == 1) {
                $template = str_replace("__REDIRECT_TYPE__", ($ini['SSL']['REDIRECT_BODY_DATA'] == 1) ? 307 : 301, $template);
                $template = str_replace("__CERTIFICATE_FILE__", $ini['SSL']['CERTIFICATE_FILE'], $template);
                $template = str_replace("__CERTIFICATE_KEY_FILE__", $ini['SSL']['CERTIFICATE_KEY_FILE'], $template);
            }
            $fp = fopen(PATH_APP . '/nginx.conf', "w");
            fwrite($fp, $template);
            fclose($fp);
            Core::logMessage("Nginx конфигурация сгенерирована успешно!", 32);

        } else Core::logMessage(basename(CFG_PATH_CLOSE) . " не найден!");
    }

    private function ssl(): void
    {
        if (file_exists(CFG_PATH_CLOSE)) {

            $ini = cfgGet();
            if ($ini['SSL']['MODE_ON']) {

                if (!is_dir('ssl')) mkdir('ssl');
                exec("openssl genrsa -des3 -out ssl/server.key 1024;
                    openssl req -new -key ssl/server.key -out ssl/server.csr;
                    openssl rsa -in ssl/server.key -out ssl/server.key;
                    openssl x509 -req -days 365 -in ssl/server.csr -signkey ssl/server.key -out ssl/server.crt");
                Core::logMessage("SSL конфигурация сгенерирована успешно!", 32);

            } else Core::logMessage("SSL модуль выключен.");
            
        } else Core::logMessage(basename(CFG_PATH_CLOSE) . " не найден!");
    }

    private function arrayToIni(array $dataArray): string
    {
        if (!is_array($dataArray)) 
            throw new \InvalidArgumentException('Function argument 2 must be an array.');

        $data = [];
        foreach ($dataArray as $key => $val) {
            if (is_array($val)) {
                $data[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    if (is_array($sval)) {
                        foreach ($sval as $_skey => $_sval) {
                            if (is_numeric($_skey)) {
                                $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper((string) $_sval) ? $_sval : '"'.$_sval.'"'));
                            } else {
                                $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper((string) $_sval) ? $_sval : '"'.$_sval.'"'));
                            }
                        }
                    } else {
                        $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper((string) $sval) ? $sval : '"'.$sval.'"'));
                    }
                }
            } else
                $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
            
            // empty line
            $data[] = null;
        }

        return implode(PHP_EOL, $data).PHP_EOL;
    }

    private function help(): void
    {
        Core::logLabel("Help");
        Core::logText(":init         -  Создать файл настроек.");
        Core::logText(":gen          -  Сгенерировать конфигурации.");
        Core::logText(":edit         -  Изменить настройки.");
        Core::logText(":show         -  Просмотр настроек.");
        Core::logText(":apache       -  Создать конфигурационный файл (Apache).");
        Core::logText(":nginx        -  Создать конфигурационный файл (Nginx).");
        Core::logText(":ssl          -  Создать конфигурационный файл (SSL).");
        Core::logLabel("End");
    }

}
