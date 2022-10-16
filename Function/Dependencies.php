<?php

use Extra\Src\Route;

function dieConnection($_error = null): never
{
    die(include PATH_PUBLIC . "/" . VIEW_ERROR . "/system.php");
}

function cfgGet(): array
{
    if (!file_exists(CFG_PATH_CLOSE)) dieConnection("Configuration file not found.");
    return json_decode(zlib_decode(hex2bin( str_replace("\n", "", file_get_contents(CFG_PATH_CLOSE)) )), true);
}

function dd(mixed ...$value): never
{
    echo '<pre style="background-color: black; color: #00ff00; border-style: solid; border-color: #ff0000; border-width: medium;">';
    print_r($value);
    echo '</pre>';
    die();
}

function parad(string $title, mixed $value = null): void
{
    echo '<pre style="background-color: black; color: #00ff00; border-style: solid; border-color: #ff0000; border-width: medium;">';
    echo "<strong style=\"color: #ffffff;\">$title</strong><br>";
    print_r($value);
    echo '</pre>';
}

function getDirContent($dir, $filter = '', &$results = array())
{
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value); 

        if(!is_dir($path)) {
            if(empty($filter) || preg_match($filter, $path)) $results[] = $path;
        } elseif($value != "." && $value != "..") {
            getDirContent($path, $filter, $results);
        }
    }

    return $results;
}

function objectToArray(object $object): array
{
    $reflectionClass = new ReflectionClass(get_class($object));
    $array = array();
    foreach ($reflectionClass->getProperties() as $property) {
        $property->setAccessible(true);
        if (strpos((string) $property->getType(), '?') !== false) {
            $value = ($property->getValue($object)) ? $property->getValue($object) : null;
        }else $value = $property->getValue($object);
        $array[$property->getName()] = $value;
        $property->setAccessible(false);
    }
    return $array;
}

function formObject(object $object): object
{
    $reflectionClass = new ReflectionClass(get_class($object));
    $array = array();
    foreach ($reflectionClass->getProperties() as $property) {
        $property->setAccessible(true);
        try {
            if (strpos((string) $property->getType(), '?') !== false) {
                $value = ($property->getValue($object)) ? $property->getValue($object) : null;
            }else $value = $property->getValue($object);
        } catch (Error) {
            $value = null;
        }
        $array[$property->getName()] = $value;
        $property->setAccessible(false);
    }
    return (object) $array;
}
// *******************
// * Imports
// *******************

function importLib(string ...$libs): void
{
    foreach ($libs as $lib) {
        include PATH_LIB ."/$lib";
    }
}

// *******************
// * End Imports
// *******************

function checkPlugin(string $plugin): bool
{
    if(empty($plugin)) return false;
    $path = PATH_PLUGIN . "/Frame.$plugin";
    return is_dir($path);
}

function bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE): string
{
    // Format string
    $format = ($format === NULL) ? '%01.2f %s' : (string) $format;

    // IEC prefixes (binary)
    if (!$si OR str_contains($force_unit, 'i')) {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $mod   = 1024;
    }
    // SI prefixes (decimal)
    else {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        $mod   = 1000;
    }

    // Determine unit to use
    if (($power = array_search((string) $force_unit, $units)) === FALSE)
    {
        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
    }

    return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
}

