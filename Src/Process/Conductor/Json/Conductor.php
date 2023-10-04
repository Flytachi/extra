<?php

namespace Extra\Src\Process\Conductor\Json;

use Extra\Src\Process\Conductor\ConductorInterface;

class Conductor implements ConductorInterface
{
    const SOURCE = PATH_STORAGE . '/process-pid.json';

    public function recordAdd(string $className, int $pid): void
    {
        if (file_exists(self::SOURCE)) {
            $data = json_decode(file_get_contents(self::SOURCE), 1);
            $data['jobs'][$className][] = $pid;
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents(self::SOURCE, $jsonData);
        } else {
            $file = fopen(self::SOURCE, "x");
            $data = ['jobs' => [$className => [$pid]]];
            fwrite($file, json_encode($data, JSON_PRETTY_PRINT));
            chmod(self::SOURCE, 0777);
        }
    }

    public function recordRemove(string $className, int $pid): void
    {
        if (file_exists(self::SOURCE)) {
            $data = json_decode(file_get_contents(self::SOURCE), 1);

            $key = array_search($pid, $data['jobs'][$className]);
            if ($key !== false) unset($data['jobs'][$className][$key]);

            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents(self::SOURCE, $jsonData);
        }
    }
}