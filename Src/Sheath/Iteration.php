<?php

namespace Extra\Src\Sheath;

use Extra\Src\Error\ExtraException;
use Extra\Src\Log\Log;

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
        $label = callableName($func);
        $attempts = 0;
        Log::debug("Iteration::callThrow => Iteration Start [attempt:{$maxAttempts}] {$label}");
        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                Log::debug("Iteration::callThrow => Calling {$attempts}");
                $func($attempts);
                return;
            } catch (\Exception $error) {
                Log::debug("Iteration::callThrow => Throw {$attempts} - "
                    . $error->getMessage() . PHP_EOL . $error->getTraceAsString());
                if ($attempts == $maxAttempts) throw $error;
                Aeon::sleepSec($sleepSecond);
            }
        }
    }

}