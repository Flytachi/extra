<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require dirname(__DIR__) . '/defines.php';

// ! Import function
foreach (glob(dirname(__FILE__)."/Function/*") as $function) require $function;
date_default_timezone_set(cfgGet()['GLOBAL_SETTING']['TIME_ZONE']);
// ! END Import

// ! Import src
foreach (glob(dirname(__FILE__)."/Src/*") as $src) require $src . '/__load__.php';
// ! END Import

// ! License
if (!softLicenseCorrect()) dieConnection('The software license is incorrect or outdated.');
// ! END License

?>