<?php

class __Cfg
{
    private $argument;
    private Array $default_configurations = array(
        'APACHE' => array(
            'SERVER_ADMIN' => 'webmaster@dummy-host.example.loc',
            'SERVER_ALIAS' => null,
            'SERVER_NAME' => null,
            'SERVER_PORT' => 80,
        ),
        'SECURITY' => array(
            'SERIAL' => null,
        ),
        'GLOBAL_SETTING' => array(
            'TIME_ZONE' => 'Asia/Samarkand',
            'SESSION_TIMEOUT' => null,
            'SESSION_LIFE' => null,
            'DEBUG' => true,
        ),
        'DATABASE' => array(
            'DRIVER' => 'mysql',
            'CHARSET' => 'utf8',
            'HOST' => 'localhost',
            'PORT' => 3306,
            'NAME' => null,
            'USER' => null,
            'PASS' => null,
        )
    );

    function __construct($value = null)
    {
        $this->argument = $value;
        $this->handle();
    }

    public function handle()
    {
        if (!is_null($this->argument)) $this->resolution();
        else $this->help();
    }

    private function resolution()
    {
        if ($this->argument == "init") $this->init();
        elseif ($this->argument == "gen") $this->generate();
        elseif ($this->argument == "edit") $this->edit();
        elseif ($this->argument == "show") $this->show();
        else echo "\033[31m"." Не такого аргумента.\n";
    }

    private function init()
    {
        if (!file_exists(CFG_PATH_CLOSE)) {
            $root = dirname(__DIR__, 4) . '/';
            $this->default_configurations['APACHE']['SERVER_ALIAS'] = basename($root);
            $this->default_configurations['APACHE']['SERVER_NAME'] = basename($root);
            $fp = fopen(CFG_PATH_OPEN, "x");
            fwrite($fp, $this->arrayToIni($this->default_configurations));
            fclose($fp);
            echo "\033[32m". " " . basename(CFG_PATH_OPEN) . " сгенерирован успешно!\n";
        }else{
            echo "\033[33m". " " . basename(CFG_PATH_CLOSE) . " уже существует!\n";
        }
    }

    private function generate()
    {
        if (file_exists(CFG_PATH_OPEN)) {
            $sett = parse_ini_file(CFG_PATH_OPEN, true);
            if (!file_exists(CFG_PATH_CLOSE)) {
                $fp = fopen(CFG_PATH_CLOSE, "x");
                fwrite($fp, chunk_split( bin2hex(zlib_encode(json_encode($sett), ZLIB_ENCODING_DEFLATE)) , 50, "\n") );
                fclose($fp);
                if (unlink(CFG_PATH_OPEN)) {
                    echo "\033[32m". " " . basename(CFG_PATH_CLOSE) . " сгенерирован успешно!\n";
                }else {
                    unlink(CFG_PATH_CLOSE);
                    echo "\033[31m"."Ошибка при генерации.\n";
                }
            }else{
                echo "\033[33m". " " . basename(CFG_PATH_CLOSE) . " уже существует!\n";
            }
        }else {
            echo "\033[33m". " " . basename(CFG_PATH_OPEN) . " не найден!\n";
        }
    }

    private function edit()
    {
        if (file_exists(CFG_PATH_CLOSE)) {
            $fp = fopen(CFG_PATH_OPEN, "x");
            fwrite($fp, $this->arrayToIni(cfgGet()));
            fclose($fp);
            unlink(CFG_PATH_CLOSE);
            echo "\033[32m". " " . basename(CFG_PATH_OPEN) . " сгенерирован успешно!\n";
        }else{
            echo "\033[33m". " " . basename(CFG_PATH_CLOSE) . " не существует!\n";
        }
    }

    private function show()
    {
        if (file_exists(CFG_PATH_CLOSE)) {
            print_r($this->arrayToIni(cfgGet()));
        }else{
            echo "\033[33m". " " . basename(CFG_PATH_CLOSE) . " не существует!\n";
        }
    }

    private function help()
    {
        echo "\033[33m"." =======> Help <======= \n";
        echo "\033[33m"."  :init     -  создать файл настроек.\n";
        echo "\033[33m"."  :gen      -  сгенерировать конфигурации.\n";
        echo "\033[33m"."  :edit     -  изменить настройки.\n";
        echo "\033[33m"."  :show     -  просмотр настроек.\n";
        echo "\033[33m"." =======> Help <======= \n";
    }

    private function arrayToIni(Array $a, Array $parent = array()): String
    {
        $out = '';
        foreach ($a as $k => $v)
        {
            if (is_array($v))
            {
                //subsection case
                $sec = array_merge((array) $parent, (array) $k);
                $out .= PHP_EOL;
                $out .= '[' . join('.', $sec) . ']' . PHP_EOL;
                $out .= $this->arrayToIni($v, $sec);
            }
            else
            {
                //plain key->value case
                $out .= "$k=$v" . PHP_EOL;
            }
        }
        return $out;
    }

}

?>