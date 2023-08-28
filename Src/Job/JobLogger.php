<?php

namespace Extra\Src\Job;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\HttpStatus;
use Extra\Src\Log\LoggerBase;
use Extra\Src\Log\LoggerInterface;

class JobLogger extends LoggerBase implements LoggerInterface
{
    public function __construct(string $className)
    {
        self::init($className);
    }

    protected static function init(string $fileName): void
    {
        if (!is_dir(PATH_LOG)) mkdir(PATH_LOG);
        if (!is_writable(PATH_LOG)) {
            $status = HttpStatus::status(HttpCode::from(500));
            header("HTTP/1.1 500 " . $status);
            header("Status: 500 " . $status);
            dd("The \"Logs\" folder does not have write access");
        }

        $exp = explode('\\', $fileName);
        $file = array_pop($exp) . '.txt';
        $dir = implode('/', $exp);

        if (!is_dir(PATH_LOG . '/' . $dir)) mkdir(PATH_LOG . '/' . $dir, 0777, true);
        $file = PATH_LOG . '/' . $dir  . '/' . $file;
        if (!file_exists($file)) {
            file_put_contents($file,'');
            chmod($file,0777);
        }
        self::$resource = fopen($file, 'a');
    }


    public static function info(string $message): void
    {
        if (self::$resource !== false) {
            $message = sprintf("[%s] %-9s | %s",
                date('r'),
                '[INFO]', $message . PHP_EOL
            );
            fwrite(self::$resource, $message);
        }
    }

    public static function warning(string $message): void
    {
        if (self::$resource !== false) {
            $message = sprintf("[%s] %-9s | %s",
                date('r'),
                '[WARNING]', $message . PHP_EOL
            );
            fwrite(self::$resource, $message);
        }
    }

    public static function error(string $message): void
    {
        if (self::$resource !== false) {
            $message = sprintf("[%s] %-9s | %s",
                date('r'),
                '[ERROR]', $message . PHP_EOL
            );
            fwrite(self::$resource, $message);
        }
    }
}