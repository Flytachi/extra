<?php

namespace Extra\Src\Process\Core\Conductor\Json;

use Extra\Src\Process\Core\Conductor\ConductorInterface;

/**
 * Class Conductor
 *
 * `Conductor` is a class that implements `ConductorInterface`. It manages running processes by adding
 * and removing records about processes in a .json file.
 *
 * The methods provided by `Conductor` include:
 *
 * - `recordAdd(string $className, int $pid): void`: Records a new process by adding the class name and process ID to the json file.
 * - `recordRemove(string $className, int $pid): void`: Removes a record of a process from the json file by class name and process ID.
 *
 * This class uses the constant SOURCE, which refers to the location of the json file that keeps the process records.
 *
 * @version 1.0
 * @author Flytachi
 */
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