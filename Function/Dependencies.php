<?php

function env(?string $name = null, string|int|float|bool|null $default = null): array|string|null
{
    $value = getenv($name);
    return $value !== false ? $value : $default;
}

function dd(mixed ...$value): never
{
    echo '<pre style="background-color: black; color: #00ff00; border-style: solid;',
        'border-color: #ff0000; border-width: medium;',
        'white-space: pre-wrap; white-space: -moz-pre-wrap;',
        'white-space: -o-pre-wrap;word-wrap: break-word;">';
    print_r($value);
    echo '</pre>';
    die();
}

function importLib(string ...$libs): void
{
    foreach ($libs as $lib) {
        include PATH_LIB ."/$lib";
    }
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

