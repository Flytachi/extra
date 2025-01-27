<?php

namespace Flytachi\Extra;

/**
 * Class Extra
 *
 * `Extra` is a helper class to manage application-level tasks such as autoload, initialization and configurations loading.
 *
 * The methods provided by `Extra` include:
 *
 * - `autoload(): void`: Handles automatic class file loading based on namespaces.
 * - `init(bool $isConsole = false): void`: Initializes the application, defines constants, loads functions, and checks directory write access.
 * - `warningHandler($severity, $message, $file, $line): void`: Error handler for managing PHP warnings.
 * - `loadFunction(): void`: Loads all available functions from the Function directory.
 *
 * @version 5.0
 * @author Flytachi
 */
class Extra
{

    public static function echo(): void
    {
        var_dump('hello world');
    }

}