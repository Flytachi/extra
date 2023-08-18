<?php

use Extra\Src\Artefact\Shard;
use Extra\Src\CDO\CDO;
use Extra\Src\Route;

class Warframe
{
    public static array $cfg;
    public static array $env;
    public static CDO|null $db = null;
    private static array $dbs = [];

    public final static function getEnv(): void
    {
        if (!file_exists(ENV_PATH)) dieConnection("Configuration file not found.");
        self::$env = parse_ini_file(PATH_APP . '/.env');
    }

    public final static function autoload(): void
    {
        spl_autoload_register(function($class) {
            $file = PATH_APP . '/' . str_replace("\\", '/', $class) . '.php';
            if (file_exists($file)) require $file;
        });
    }

    public final static function init(): void
    {
        require dirname(__DIR__) . '/defines.php';
        self::loadFunction();
        self::autoload();
        self::getEnv();

        require dirname(__DIR__) . '/database.php';
        if (PHP_SAPI === "cli-server") $_SERVER['REQUEST_SCHEME'] = "http";
        define('SERVER_SCHEME', $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME']);
        date_default_timezone_set(self::$env['TIME_ZONE']);
//        if (!Warframe::softLicenseCorrect()) Route::Throwable(510, 'The software license is incorrect or outdated.');
    }
    
    public final static function coreInit(): void
    {
        require dirname(__DIR__) . '/defines.php';
        self::loadFunction();
        self::autoload();
        self::getEnv();

        if (PHP_SAPI === "cli-server") $_SERVER['REQUEST_SCHEME'] = "http";
        require dirname(__DIR__) . '/database.php';
        date_default_timezone_set(self::$env['TIME_ZONE']);
    }

    public final static function loadFunction(): void
    {
        foreach (glob(dirname(__FILE__)."/Function/*") as $function) require $function;   
    }

    public final static function softLicenseCorrect(): bool
    {
        if (Warframe::$env['SECURITY_PRODUCT_GUARD']) {
            $license = licenseKey();
            $toDay = strtotime(date('Y-m-d'));
            if (
                (Warframe::$env['SECURITY_PRODUCT_FIRMWARE'] . '-' . motherboardSeries() === $license->licenseFirmware . '-' . $license->motherboardSeries)
                and ($license->licenseDateFrom <= $toDay and $toDay <= $license->licenseDateTo)
            ) return true;
            else return false;
        } else return true;
    }

    public final static function setDb(string $key, Shard $shard): void
    {
        if (!array_key_exists($key, self::$dbs)) {
            self::$dbs[$key] = new CDO($shard, self::$env['DEBUG']);
        }
    }

    public final static function db(?string $key = null): CDO
    {
        return ($key) ? self::$dbs[$key] : reset(self::$dbs);
    }

}
