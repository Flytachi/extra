<?php

/**
 * Copies files and directories from the source directory to the destination directory recursively.
 *
 * @param string $source The path to the source directory.
 * @param string $dest The path to the destination directory.
 * @param bool $over An optional flag to indicate whether existing files and directories in the destination directory should be overwritten. Defaults to false.
 *
 * @return void
 */
function multiCopy(string $source, string $dest, bool $over = false): void
{
    if(!is_dir($dest)) mkdir($dest);
    if($handle = opendir($source))
    {
        while(false !== ($file = readdir($handle)))
        {
            if($file != '.' && $file != '..')
            {
                $path = $source . '/' . $file;
                if(is_file($path)) {
                    if(!is_file($dest . '/' . $file || $over)) if(!@copy($path, $dest . '/' . $file)) echo "('.$path.') Ошибка!!! ";
                } elseif(is_dir($path)) {
                    if(!is_dir($dest . '/' . $file)) mkdir($dest . '/' . $file);
                    multiCopy($path, $dest . '/' . $file, $over);
                }
            }
        }
        closedir($handle);
    }
}


/**
 * Flushes a directory recursively, deleting all its files and subdirectories.
 *
 * @param string $dirPath The path to the directory to be flushed.
 * @param string $rootDirPath The path to the root directory.
 * @param array $excludedDirPaths An optional array of directory paths to be excluded from deletion.
 * @param array $excludedFileNames An optional array of file names to be excluded from deletion.
 * @param callable|null $callback An optional callback function to be called for each deleted file or directory. The function must accept an associative array as an argument, containing
 * the "path", "status" and "is_dir" keys.
 *
 * @return void
 */
function flushDirectory(string $dirPath, string $rootDirPath, array $excludedDirPaths = [], array $excludedFileNames = [], ?callable $callback = null): void
{
    $relPath = trim(str_replace($rootDirPath, '', $dirPath), '/\\');
    $excludedDirPaths = array_map(fn($path) => trim($path, '/\\'), $excludedDirPaths);
    $excludedFileNames = array_map(fn($path) => trim($path, '/\\'), $excludedFileNames);
    if (in_array($relPath, $excludedDirPaths)) return;
    $files = array_diff(scandir($dirPath), array('.','..'));

    foreach ($files as $file) {
        if(is_file("$dirPath/$file")) {
            if(!in_array(basename($file), $excludedFileNames)) {
                $unlinkStatus = unlink("$dirPath/$file");
                if($callback !== null)
                    call_user_func($callback, ['path' => $relPath . '/' . $file, 'status' => $unlinkStatus, 'is_dir' => false]);
            }
        }
        elseif (is_dir("$dirPath/$file")) flushDirectory("$dirPath/$file", $rootDirPath, $excludedDirPaths, $excludedFileNames, $callback);
    }
    if ($dirPath != $rootDirPath) {
        $rmdirStatus = rmdir($dirPath);
        if($callback !== null)
            call_user_func($callback, ['path' => $relPath, 'status' => $rmdirStatus, 'is_dir' => true]);
    }
}
