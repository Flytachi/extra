<?php

function env(?string $name = null, string|int|float|bool|null $default = null): array|string|null
{
    $value = getenv($name);
    return $value !== false ? $value : $default;
}

function dd(mixed ...$values): never
{
    $backtrace = debug_backtrace();
    $line = $backtrace[0]['line'];
    $file = str_replace(PATH_ROOT, '', $backtrace[0]['file']);
    echo '<body style="background-color: #0a0f1f">';
    echo '<div style="border: 2px solid #3e006f;border-radius: 7px;padding: 10px;background-color: black;">';
    echo    '<div style="display: flex;justify-content: space-between;margin-top: 8px;margin-bottom: 17px">';
    echo        '<span style="float: left;font-size: 1.2rem; color: #ffffff;">';
    echo            '<span style="color: #7f00e0;font-weight: bold;">DUMP and DIE:</span> ' . $file . ' (' . $line . ')';
    echo        '</span>';
    echo        '<span style="float: right;font-style: italic;">';
    echo            '<span style="color: #adadad">' . date(DATE_ATOM) . '</span> ';
    echo            '<span style="color: #00ffff">' . env('TIME_ZONE', 'UTC') . '</span>';
    echo        '</span>';
    echo    '</div>';
    echo    '<hr style="border: 1px solid #999999;">';
    echo    '<pre style="margin:10px;white-space: pre-wrap; white-space: -moz-pre-wrap;white-space: -o-pre-wrap;word-wrap: break-word;">';
    $countValues = count($values); $i = 0;
    foreach ($values as $value) {
        echo match (gettype($value)) {
            'NULL'               => '<span style="color: #999999;">null</span>',
            'boolean'            => '<span style="color: #00ff00;">' . var_export($value, true) . '</span>',
            'integer', 'double'  => '<span style="color: #00ffff;">' . var_export($value, true) . '</span>',
            'object'             => '<span style="color: #ff7033;">' . print_r($value, true) . '</span>',
            'array'              => '<span style="color: #cb71ff;">' . print_r($value, true) . '</span>',
            'string'             => '<span style="color: #e4ff6c;">' . var_export($value, true) . '</span>',
            default              => '<span style="color: #fa5151;">' . var_export($value, true) . '</span>'
        };
        if ($countValues > ++$i) echo '<hr style="border: 1px dashed rgb(68,68,68);">';
    }
    echo    '</pre>';
    echo    '<hr style="border: 1px solid #999999;">';
    echo    '<span style="color: #9e9e9e;font-weight: bold;">Memory ' . bytes(memory_get_usage(), 'MiB') . '</span>';
    echo '</div>';
    echo '</body>';
    die();
}

function dump(mixed ...$value): never
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

function callableName(callable $callable): string
{
    return match (true) {
        is_string($callable) && strpos($callable, '::') => '[static] ' . $callable,
        is_string($callable) => '[function] ' . $callable,
        is_array($callable) && is_object($callable[0]) => '[method] ' . get_class($callable[0])  . '->' . $callable[1],
        is_array($callable) => '[static] ' . $callable[0]  . '::' . $callable[1],
        $callable instanceof Closure => '[closure]',
        is_object($callable) => '[invokable] ' . get_class((object) $callable),
        default => '[unknown]'
    };
}

function scanFindAllFile(string $rootDir, ?string $extension = null): array
{
    $files = [];

    // Получаем все файлы и директории
    $items = glob($rootDir . '/*');

    foreach ($items as $item) {
        if (is_dir($item)) {
            // Рекурсивный вызов для поддиректорий
            $files = array_merge($files, scanFindAllFile($item));
        } else {
            if ($extension != null && pathinfo($item, PATHINFO_EXTENSION) === 'php') {
                $files[] = $item;
            } elseif($extension == null) $files[] = $item;
        }
    }

    return $files;
}
