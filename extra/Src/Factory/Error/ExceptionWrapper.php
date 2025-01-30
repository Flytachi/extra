<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Error;

use Flytachi\Extra\Src\Factory\Http\Header;
use Flytachi\Extra\Src\Unit\File\XML;

class ExceptionWrapper
{
    public static function wrapHeader(): array
    {
        $accept = Header::getHeader('Accept');
        if (str_contains($accept, 'text/html')) {
            return ['Content-Type' => 'text/html; charset=utf-8'];
        } else {
            return ['Content-Type' => $accept];
        }
    }

    public static function wrapBody(\Throwable $throwable): string
    {
        return match (Header::getHeader('Accept')) {
            'application/json' => self::constructJson($throwable),
            'application/xml' => self::constructXml($throwable),
            default => self::constructDefault($throwable)
        };
    }

    public static function constructJson(\Throwable $throwable): string
    {
        $context = [
            'code' => $throwable->getCode(),
            'message' => $throwable->getMessage()
        ];

        if (env('DEBUG', false)) {
            $context['exception'] = [
                'name' => $throwable::class,
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTrace(),
            ];
        }

        return json_encode($context);
    }

    public static function constructXml(\Throwable $throwable): string
    {
        $context = [
            'code' => $throwable->getCode(),
            'message' => $throwable->getMessage()
        ];

        if (env('DEBUG', false)) {
            $context['exception'] = [
                'name' => $throwable::class,
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTrace(),
            ];
        }

        return XML::arrayToXml($context);
    }

    public static function constructDefault(\Throwable $throwable): string
    {
        if (env('DEBUG', false)) {
            $tColor = match ((int)($throwable->getCode() / 100)) {
                1 => "00ffff",
                2 => "00ff00",
                3 => "ff00e0",
                4 => "ffff00",
                5 => "ff0000",
                default => "dddddd",
            };

            if (EXTRA_STARTUP_TIME !== null) {
                $delta = round(microtime(true) - EXTRA_STARTUP_TIME, 3);
                $delta = ($delta < 0.001) ? 0.001 : $delta;
            } else {
                $delta = null;
            }

            $message = [];
            self::forThrow($message, $throwable);

            $result  = '<body style="background-color: #0a0f1f">';
            $result .= '<div style="border: 2px solid #' . $tColor
                . ';border-radius: 7px;padding: 10px;background-color: black;">';
            $result .=    '<div style="display: flex;justify-content: space-between;'
                . 'margin-top: 8px;margin-bottom: 17px">';
            $result .=        '<span style="float: left;font-size: 1.2rem; color: #ffffff;">';
            $result .=            '<span style="color: #' . $tColor . ';font-weight: bold;">[' . $throwable->getCode()
                . '] Extra Debug Message:</span> ' . $throwable::class;
            $result .=        '</span>';
            $result .=        '<span style="float: right;font-style: italic;">';
            $result .=            '<span style="color: #adadad">' . date(DATE_ATOM) . '</span> ';
            $result .=            '<span style="color: #00ffff">' . env('TIME_ZONE', 'UTC') . '</span>';
            $result .=        '</span>';
            $result .=    '</div>';
            $result .=    '<hr style="border: 1px solid #999999;">';
            $result .=    '<pre style="margin:10px; white-space: pre-wrap; '
                . 'white-space: -moz-pre-wrap;white-space: -o-pre-wrap;word-wrap: break-word;">';
            $result .=      '<span style="color: #' . $tColor . ';font-size: 1.1rem;font-weight: bold;">'
                . $throwable->getMessage() . '</span><br>';
            foreach ($message as $msg) {
                $result .=  '<span style="color: #f1f1f1;">' . print_r($msg, true) . '</span><br>';
            }
            $result .=      '<span style="color: #fd2929;font-size: 1.2rem;font-weight: bold;">DETAIL</span><br>';
            $result .=      '<span style="color: #fa5151;">' . print_r($throwable, true) . '</span><br>';
            $result .=    '</pre>';
            $result .=    '<hr style="border: 1px solid #999999;">';
            $result .=    '<div style="display: flex;justify-content: space-between;">';
            $result .=        '<span style="float: left;color: #9e9e9e;font-weight: bold;">Memory '
                . bytes(memory_get_usage(), 'MiB') . '</span>';
            $result .=        '<span style="float: right;color: #9e9e9e;font-style: italic;">Time '
                . $delta . '</span>';
            $result .=    '</div>';
            $result .= '</div>';
            $result .= '</body>';
        } else {
            $result = '<strong>Extra Error ' . $throwable->getCode() . ':</strong> ' . $throwable->getMessage();
        }

        return $result;
    }

    private static function forThrow(array &$message, \Throwable $throwable): void
    {
        $previous = $throwable->getPrevious();
        if ($previous) {
            self::forThrow($message, $previous);
        }
        foreach ($throwable->getTrace() as $key => $value) {
            $ms = "#{$key} ";
            if ($key == 0) {
                $ms .= $value['file'] ?? $throwable->getFile();
                $ms .= ' (' . ($value['line'] ?? $throwable->getLine()) . '): ';
            } else {
                if (isset($value['file'])) {
                    $ms .= $value['file'];
                }
                if (isset($value['line'])) {
                    $ms .= ' (' . $value['line'] . '): ';
                }
            }
            if (isset($value['class'])) {
                $ms .= $value['class'];
            }
            if (isset($value['type'])) {
                $ms .= $value['type'];
            }
            if (isset($value['function'])) {
                $ms .= $value['function'];
            }
            $message[] = $ms;
        }
    }
}
