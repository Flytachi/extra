<?php

class __Key
{
    private $argument;
    private $serial;

    function __construct($value = null, $serial = null)
    {
        $this->argument = $value;
        $this->serial = $serial;
        $this->handle();
    }

    private function handle()
    {
        if (!is_null($this->argument) and !is_null($this->serial)) $this->resolution();
        else $this->error();
    }

    private function resolution()
    {
        if (hex2bin("$this->argument") === "master_key_ITACHI:2021-06-30") $this->generate_key();
        else $this->error();
    }

    private function generate_key()
    {
        $KEY = dirname(__DIR__, 3) . "/.key";
        $fp = fopen($KEY, "w");
        fwrite($fp, bin2hex(zlib_encode($this->serial, ZLIB_ENCODING_DEFLATE)));
        fclose($fp);
        echo "[security->" . date("Y-m-d H:i:s") . "] Done";
    }

    private function error()
    {
        unlink(dirname(__DIR__, 3) . "/.key");
        echo "[security->" . date("Y-m-d H:i:s") . "] Fail";
    }

}

?>