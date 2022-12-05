<?php

enum METHOD
{
    case GET;
    case HEAD;
    case POST;
    case PUT;
    case DELETE;
    case CONNECT;
    case OPTIONS;
    case TRACE;
    case PATCH;
}

class Warframe
{
    public static array $cfg;

    public final static function loader(): void
    {
        if (PHP_SAPI === "cli-server") $_SERVER['REQUEST_SCHEME'] = "http";
        
        require dirname(__DIR__) . '/defines.php';
        
        define('SERVER_SCHEME', $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME']);

        Warframe::loadFunction();
        Warframe::$cfg = cfgGet();
        Warframe::loadSrc();
        if (!Warframe::softLicenseCorrect()) dieConnection('The software license is incorrect or outdated.');
    }

    public final static function loadFunction(): void
    {
        foreach (glob(dirname(__FILE__)."/Function/*") as $function) require $function;
        date_default_timezone_set(cfgGet()['GLOBAL_SETTING']['TIME_ZONE']);
    }

    public final static function loadSrc(): void
    {
        spl_autoload_register(function($class) {
            $file = dirname(__FILE__, 2) .'/'. lcfirst(str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.class.php');
            if (file_exists($file)) {
                require $file;
                return true;
            } else return false; 
        });
    }

    public final static function softLicenseCorrect(): bool
    {
        if (Warframe::$cfg['SECURITY']['PRODUCT_GUARD']) {
            $license = licenseKey();
            $toDay = strtotime(date('Y-m-d'));
            if (
                (Warframe::$cfg['SECURITY']['PRODUCT_FIRMWARE'] . '-' . motherboardSeries() === $license->licenseFirmware . '-' . $license->motherboardSeries)
                and ($license->licenseDateFrom <= $toDay and $toDay <= $license->licenseDateTo)
            ) return true;
            else return false;
        } else return true;
    }

}
