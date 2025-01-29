<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(?string $name = null, bool|int|float|string|null $default = null): bool|int|float|string|null
    {
        if (!isset($_ENV[$name])) {
            return $default ?? null;
        }
        $value = $_ENV[$name];
        if (is_string($value)) {
            if (strtolower($value) === 'true') {
                return true;
            } elseif (strtolower($value) === 'false') {
                return false;
            }

            if (is_numeric($value)) {
                if (str_contains($value, '.')) {
                    return (float)$value;
                }
                // Иначе преобразуем в int
                return (int)$value;
            }
        }
        return $value;
    }
}

if (!function_exists('bytes')) {
    function bytes($bytes, $force_unit = null, $format = null, $si = true): string
    {
        // Format string
        $format = ($format === null) ? '%01.2f %s' : (string) $format;

        // IEC prefixes (binary)
        if (!$si or str_contains($force_unit, 'i')) {
            $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
            $mod   = 1024;
        }
        // SI prefixes (decimal)
        else {
            $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
            $mod   = 1000;
        }

        // Determine unit to use
        if (($power = array_search((string) $force_unit, $units)) === false) {
            $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
        }

        return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
    }

}

if (!function_exists('dd')) {
    function dd(mixed ...$values): never
    {
        $backtrace = debug_backtrace();
        $line = $backtrace[0]['line'];
        $file = $backtrace[0]['file'];

        if (EXTRA_STARTUP_TIME !== null) {
            $delta = round(microtime(true) - EXTRA_STARTUP_TIME, 3);
            $delta = ($delta < 0.001) ? 0.001 : $delta;
        } else {
            $delta = null;
        }

        echo '<body style="background-color: #0a0f1f">';
        echo '<div style="border: 2px solid #3e006f;border-radius: 7px;padding: 10px;background-color: black;">';
        echo    '<div style="display: flex;justify-content: space-between;margin-top: 8px;margin-bottom: 17px">';
        echo        '<span style="float: left;font-size: 1.2rem; color: #ffffff;">';
        echo sprintf("<span style=\"color: #7f00e0;font-weight: bold;\">DUMP and DIE:</span> %s (%s)", $file, $line);
        echo        '</span>';
        echo        '<span style="float: right;font-style: italic;">';
        echo            '<span style="color: #adadad">' . date(DATE_ATOM) . '</span> ';
        echo            '<span style="color: #00ffff">' . env('TIME_ZONE', 'UTC') . '</span>';
        echo        '</span>';
        echo    '</div>';
        echo    '<hr style="border: 1px solid #999999;">';
        echo    '<pre style="margin:10px;white-space: pre-wrap; ';
        echo    'white-space: -moz-pre-wrap;white-space: -o-pre-wrap;word-wrap: break-word;">';
        $countValues = count($values);
        $i = 0;
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
            if ($countValues > ++$i) {
                echo '<hr style="border: 1px dashed rgb(68,68,68);">';
            }
        }
        echo    '</pre>';
        echo    '<hr style="border: 1px solid #999999;">';
        echo    '<div style="display: flex;justify-content: space-between;">';
        echo        '<span style="float: left;color: #9e9e9e;font-weight: bold;">Memory '
                            . bytes(memory_get_usage(), 'MiB') . '</span>';
        echo        '<span style="float: right;color: #9e9e9e;font-style: italic;">Time '
                            . $delta . '</span>';
        echo    '</div>';
        echo '</div>';
        echo '</body>';
        die();
    }
}

if (!function_exists('scanFindAllFile')) {
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
                } elseif ($extension == null) {
                    $files[] = $item;
                }
            }
        }

        return $files;
    }
}
