<?php

namespace Extra\Src\Error;

use Extra\Src\Entity\Request\Request;
use Extra\Src\HttpCode;
use Extra\Src\Log\Log;

/**
 * Class ExtraException
 *
 * `ExtraException` is an abstract class that extends the base Exception class and provides additional
 * functionality, including error handling and debugging information. It uses different responses for error display: plain text and JSON format.
 *
 * The methods provided by `ExtraException` include:
 *
 * - `__toString(): string`: Converts the `ExtraException` to a string and sends the error report.
 * - `debugApi(): array`: Provides the API debug information.
 * - `getThrowableText(): string`: Generates the plain text for the exception message.
 * - `getThrowableJson(): string`: Generates the JSON representation of the exception.
 * - `forThrow(array|string &$message, Throwable $throwable): void`: Constructs and collects the exception stack trace.
 *
 * @version 2.0
 * @author Flytachi
 */
abstract class ExtraException extends \Exception implements ErrorInterface
{
    protected string $handle = 'Extra';

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
            if (Request::inHeader('Accept', 'application/json')) return $this->getThrowableJson();
            else return $this->getThrowableText();
        }
    }

    protected function debugApi(): array
    {
        if (env('DEBUG', false)) {
            $message = [];
            $this->forThrow($message, $this);

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
        $status = HttpCode::from($this->code)->message();
        header("HTTP/1.1 {$this->code} " . $status);
        header("Status: {$this->code} " . $status);
        header_remove("X-Powered-By");

        if (env('DEBUG', false)) {
//            dd($this);
            $tColor = match ((int)($this->code / 100)) {
                1 => "00ffff",
                2 => "00ff00",
                3 => "ff00e0",
                4 => "ffff00",
                5 => "ff0000",
                default => "dddddd",
            };

            $message = [];
            $this->forThrow($message, $this);

            $result  = '<body style="background-color: #0a0f1f">';
            $result .= '<div style="border: 2px solid #' . $tColor . ';border-radius: 7px;padding: 10px;background-color: black;">';
            $result .=    '<div style="display: flex;justify-content: space-between;margin-top: 8px;margin-bottom: 17px">';
            $result .=        '<span style="float: left;font-size: 1.2rem; color: #ffffff;">';
            $result .=            '<span style="color: #' . $tColor . ';font-weight: bold;">[' . $this->code . '] Extra Debug Message:</span> ' . $this->handle;
            $result .=        '</span>';
            $result .=        '<span style="float: right;font-style: italic;">';
            $result .=            '<span style="color: #adadad">' . date(DATE_ATOM) . '</span> ';
            $result .=            '<span style="color: #00ffff">' . env('TIME_ZONE', 'UTC') . '</span>';
            $result .=        '</span>';
            $result .=    '</div>';
            $result .=    '<hr style="border: 1px solid #999999;">';
            $result .=    '<pre style="white-space: pre-wrap; white-space: -moz-pre-wrap;white-space: -o-pre-wrap;word-wrap: break-word;">';
            foreach ($message as $msg)
                $result .=  '<span style="color: #f1f1f1;">' . print_r($msg, true) . '</span><br>';
            $result .=      '<span style="color: #fd2929;font-size: 1.2rem;font-weight: bold;">DETAIL</span><br>';
            $result .=      '<span style="color: #fa5151;">' . print_r($this, true) . '</span><br>';
            $result .=    '</pre>';
            $result .=    '<hr style="border: 1px solid #999999;">';
            $result .=    '<span style="color: #9e9e9e;font-weight: bold;">Memory ' . bytes(memory_get_usage(), 'MiB') . '</span>';
            $result .= '</div>';
            $result .= '</body>';
            return $result;
        } else {
            $page = PATH_RESOURCE . "/exception/{$this->code}.php";
            if (file_exists($page)) include $page;
            else {
                $page = PATH_RESOURCE . '/exception/system.php';
                $_error = $this->code . ' ' . $status;
                if (file_exists($page)) include $page;
                else include PATH_APP . '/Extra/Console/Template/Resource/exception/system';
            }
            return '';
        }
    }

    protected function getThrowableJson(): string
    {
        $status = HttpCode::from($this->code)->message();
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

    protected function forThrow(array|string &$message, \Throwable $throwable): void
    {
        $previous = $throwable->getPrevious();
        if($previous) $throwable->forThrow($message, $previous);
        if (is_array($message)) {
            foreach ($throwable->getTrace() as $key => $value) {
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
        } else {
            foreach ($throwable->getTrace() as $key => $value) {
                if ($key == 0) {
                    $message .= "\n\t\t#" . $key . ' ';
                    $message .= $value['file'] ?? $throwable->file;
                    $message .= ' (' . ($value['line'] ?? $throwable->line) . '): ';
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
        }

    }
}