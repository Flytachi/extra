<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Unit;

use Closure;
use Flytachi\Extra\Extra;

/**
 *  Extra collection
 *
 *  Iteration
 *
 *  @package Extra\Src\Sheath
 *  @version 1.0
 *  @author itachi
 */
class Iteration
{
    /**
     * @param int $maxAttempts
     * @param callable $func
     * @param int $sleepSecond
     * @return void
     * @throws \Exception
     */
    public static function callThrow(int $maxAttempts, callable $func, int $sleepSecond = 0): void
    {
        $label = self::callableName($func);
        $attempts = 0;
        Extra::$logger->withName("Iteration::callThrow")->debug("Iteration Start [attempt:{$maxAttempts}] {$label}");
        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                Extra::$logger->withName("Iteration::callThrow")->debug("Calling {$attempts}");
                $func($attempts);
                return;
            } catch (\Throwable $error) {
                Extra::$logger->withName("Iteration::callThrow")->debug("Throw {$attempts} - "
                    . $error->getMessage() . PHP_EOL . $error->getTraceAsString());
                if ($attempts == $maxAttempts) {
                    throw new UnitException($error->getMessage(), $error->getCode(), $error);
                }
                TimeTool::sleepSec($sleepSecond);
            }
        }
    }

    public static function callableName(callable $callable): string
    {
        return match (true) {
            is_string($callable) && strpos($callable, '::') => '[static] ' . $callable,
            is_string($callable) => '[function] ' . $callable,
            is_array($callable) && is_object($callable[0])
                => '[method] ' . get_class($callable[0])  . '->' . $callable[1],
            is_array($callable) => '[static] ' . $callable[0]  . '::' . $callable[1],
            $callable instanceof Closure => '[closure]',
            is_object($callable) => '[invokable] ' . get_class((object) $callable),
            default => '[unknown]'
        };
    }
}
