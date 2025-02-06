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


if (!function_exists('flushDirectory')) {
    /**
     * Flushes a directory recursively, deleting all its files and subdirectories.
     *
     * @param string $dirPath The path to the directory to be flushed.
     * @param string $rootDirPath The path to the root directory.
     * @param array $excludedDirPaths An optional array of directory paths to be excluded from deletion.
     * @param array $excludedFileNames An optional array of file names to be excluded from deletion.
     * @param callable|null $callback An optional callback function to be called for each deleted file or
     * directory. The function must accept an associative array as an argument, containing
     * the "path", "status" and "is_dir" keys.
     *
     * @return void
     */
    function flushDirectory(
        string $dirPath,
        string $rootDirPath,
        array $excludedDirPaths = [],
        array $excludedFileNames = [],
        ?callable $callback = null
    ): void {
        $relPath = trim(str_replace($rootDirPath, '', $dirPath), '/\\');
        $excludedDirPaths = array_map(fn($path) => trim($path, '/\\'), $excludedDirPaths);
        $excludedFileNames = array_map(fn($path) => trim($path, '/\\'), $excludedFileNames);
        if (in_array($relPath, $excludedDirPaths)) {
            return;
        }
        $files = array_diff(scandir($dirPath), array('.','..'));

        foreach ($files as $file) {
            if (is_file("$dirPath/$file")) {
                if (!in_array(basename($file), $excludedFileNames)) {
                    $unlinkStatus = unlink("$dirPath/$file");
                    if ($callback !== null) {
                        call_user_func($callback, [
                            'path' => $relPath . '/' . $file,
                            'status' => $unlinkStatus,
                            'is_dir' => false
                        ]);
                    }
                }
            } elseif (is_dir("$dirPath/$file")) {
                flushDirectory("$dirPath/$file", $rootDirPath, $excludedDirPaths, $excludedFileNames, $callback);
            }
        }
        if ($dirPath != $rootDirPath) {
            $rmdirStatus = rmdir($dirPath);
            if ($callback !== null) {
                call_user_func($callback, ['path' => $relPath, 'status' => $rmdirStatus, 'is_dir' => true]);
            }
        }
    }
}

if (!function_exists('multiCopy')) {
    /**
     * Copies files and directories from the source directory to the destination directory recursively.
     *
     * @param string $source The path to the source directory.
     * @param string $dest The path to the destination directory.
     * @param bool $over An optional flag to indicate whether existing files and directories
     * in the destination directory should be overwritten. Defaults to false.
     *
     * @return void
     */
    function multiCopy(string $source, string $dest, bool $over = false): void
    {
        if (!is_dir($dest)) {
            mkdir($dest);
        }
        if ($handle = opendir($source)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $path = $source . '/' . $file;
                    if (is_file($path)) {
                        if (!is_file((string) ($dest . '/' . $file || $over))) {
                            if (!@copy($path, $dest . '/' . $file)) {
                                echo "('.$path.') Ошибка!!! ";
                            }
                        }
                    } elseif (is_dir($path)) {
                        if (!is_dir($dest . '/' . $file)) {
                            mkdir($dest . '/' . $file);
                        }
                        multiCopy($path, $dest . '/' . $file, $over);
                    }
                }
            }
            closedir($handle);
        }
    }
}
