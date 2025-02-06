<?php

declare(strict_types=1);

namespace Flytachi\Extra\Console;

use Flytachi\Extra\Console\Inc\CoreHandle;

class Core extends CoreHandle
{
    public function __construct($args)
    {
        $this->parser($args);
        $this->cluster();
    }

    private function cluster(): void
    {
        try {
            if (array_key_exists(0, self::$arguments['arguments'])) {
                $cmd = ucwords(self::$arguments['arguments'][0]);
            } else {
                $cmd = 'Help';
            }
            ('Flytachi\Extra\Console\Command\\' . $cmd)::script(self::$arguments);
        } catch (\Throwable $exception) {
            self::printError($exception);
        }
    }
}
