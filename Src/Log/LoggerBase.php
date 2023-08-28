<?php

namespace Extra\Src\Log;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\HttpStatus;

abstract class LoggerBase {
    /**
     * @var false|resource $resource
     */
    protected static $resource;

    protected static function init(string $fileName): void
    {
        if (!is_dir(PATH_LOG)) mkdir(PATH_LOG);
        if (!is_writable(PATH_LOG)) {
            $status = HttpStatus::status(HttpCode::from(500));
            header("HTTP/1.1 500 " . $status);
            header("Status: 500 " . $status);
            dd("The \"Logs\" folder does not have write access");
        }
        $file = PATH_LOG . '/' . $fileName . '.txt';
        if (!file_exists($file)) {
            file_put_contents($file,'');
            chmod($file,0777);
        }
        self::$resource = fopen($file, 'a');
    }
}