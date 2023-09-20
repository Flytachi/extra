<?php

namespace Extra\Src\Error;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\HttpStatus;
use Extra\Src\Enum\Request;
use Extra\Src\Log\Log;

abstract class ExtraException extends \Exception implements ErrorInterface
{
    protected string $handle = 'Warframe';

    public function __toString(): string
    {
        // Logging
        $logMessage = env('DEBUG', false) ?  ($this->getMessage() . "\n" . $this->getTraceAsString()) : $this->getMessage();
        $statusGroup = (int)($this->code / 100);
        if ($statusGroup == 5) Log::error($logMessage);
        elseif ($statusGroup == 4) Log::warning($logMessage);
        elseif ($statusGroup == 7) Log::critical($logMessage);
        else Log::alert($logMessage);

        if (PHP_SAPI === 'cli') return parent::__toString();
        else {
            if (Request::getHeader('Accept') == 'application/json') die($this->getThrowableJson());
            else die($this->getThrowableText());
        }
    }

    protected function debugApi(): array
    {
        if (env('DEBUG', false)) {
            $message = [];
            foreach ($this->getTrace() as $key => $value) {
                $ms = "#{$key} ";
                if ($key == 0) {
                    $ms .= $value['file'] ?? $this->file;
                    $ms .= ' (' . ($value['line'] ?? $this->line) . '): ';
                    if (isset($value['class'])) $ms .= $value['class'];
                    if (isset($value['type'])) $ms .= $value['type'];
                    if (isset($value['function'])) $ms .= $value['function'];
                    $message[] = $ms;
                } else {
                    if (isset($value['file'])) $ms .= $value['file'];
                    if (isset($value['line'])) $ms .= ' (' . $value['line'] . '): ';
                    if (isset($value['class'])) $ms .= $value['class'];
                    if (isset($value['type'])) $ms .= $value['type'];
                    if (isset($value['function'])) $ms .= $value['function'];
                    $message[] = $ms;
                }
            }

            $delta = round(microtime(true)-$_SERVER['REQUEST_TIME'], 3);
            return [
                'debug' => [
                    'time' => ($delta < 0.001) ? 0.001 : $delta,
                    'date' => date(DATE_ATOM),
                    'timezone' => env('TIME_ZONE', 'UTC'),
                    'sapi' => PHP_SAPI,
                    'memory' => bytes(memory_get_usage(), 'MiB'),
                ],
                'exception' => $message
            ];
        } else return [];
    }

    protected function getThrowableText(): string
    {
        $status = HttpStatus::status(HttpCode::from($this->code));
        header("HTTP/1.1 {$this->code} " . $status);
        header("Status: {$this->code} " . $status);
        header_remove("X-Powered-By");

        if (env('DEBUG', false)) {
            $tColor = match ((int)($this->code / 100)) {
                1 => "00ffff",
                2 => "00ff00",
                3 => "ff00e0",
                4 => "ffff00",
                5 => "ff0000",
                default => "dddddd",
            };

            $message = "";
            foreach ($this->getTrace() as $key => $value) {
                if ($key == 0) {
                    $message .= "\n\t\t#" . $key . ' ';
                    $message .= $value['file'] ?? $this->file;
                    $message .= ' (' . ($value['line'] ?? $this->line) . '): ';
                    if (isset($value['class'])) $message .= "\t" . $value['class'];
                    if (isset($value['type'])) $message .= $value['type'];
                    if (isset($value['function'])) $message .= $value['function'];
                } else {
                    $message .= "\n\t\t#" . $key . ' ';
                    if (isset($value['file'])) $message .= $value['file'];
                    if (isset($value['line'])) $message .= ' (' . $value['line'] . '): ';
                    if (isset($value['class'])) $message .= "\t" . $value['class'];
                    if (isset($value['type'])) $message .= $value['type'];
                    if (isset($value['function'])) $message .= $value['function'];
                }
            }

            return "<pre style=\"background-color: black; color: #{$tColor}; border-style: solid; border-color: #ff0000; border-width: medium; padding:7px; padding-top:13px\">"
                . "<strong style=\"font-size:16px; color: #ffffff;\"> Warframe Debug Message | " . $this->handle . "</strong><hr>"
                . "\t <strong style=\"font-size:14px;\">" . $this->message . "</strong>"
                . $message
                . "<hr></pre>";
        }
        else {
            $page = PATH_RESOURCE . "/exception/{$this->code}.php";
            if (file_exists($page)) die( include $page );
            else {
                $_error = $this->code . ' ' . $status;
                return include PATH_RESOURCE . '/exception/system.php';
            }
        }
    }

    protected function getThrowableJson(): string
    {
        $status = HttpStatus::status(HttpCode::from($this->code));
        header("HTTP/1.1 {$this->code} " . $status);
        header("Status: {$this->code} " . $status);
        header_remove("X-Powered-By");
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");
        $debug = $this->debugApi();
        return json_encode([
            'statusCode' => $this->code,
            'statusDescription' => $status,
            'message' => $this->message,
            ...$debug
        ]);
    }
}