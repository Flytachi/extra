<?php

namespace Extra\Console\Command;

use Extra\Console\Inc\Cmd;
use Extra\Src\Artefact\Aegis;
use Extra\Src\Artefact\Mechanism\Shard;

class Db extends Cmd
{
    public static string $title = "command database control";
    private string $pathStore = PATH_APP . '/Config/store';
    private ?Shard $shard = null;

    public function handle(): void
    {
        self::printTitle("Db", 32);

        if (
            count($this->args['arguments']) > 1
        ) $this->resolution();
        else {
            self::printMessage("Enter argument");
            self::print("Example: extra db compare");
        }

        self::printTitle("Db", 32);
    }

    private function resolution(): void
    {
        if (array_key_exists(1, $this->args['arguments'])) {
            $this->shard = Aegis::getShard($this->args['options']['shard'] ?? 'db');
            switch ($this->args['arguments'][1]) {
                case 'skeleton': $this->skeleton(); break;
//                case 'migrate': $this->migrate(); break;
//                case 'compare': $this->compare(); break;
                default:
                    self::printMessage("Argument '{$this->args['arguments'][1]}' not found");
                    break;
            }
        }
    }

    private function skeleton(): void
    {
        if (!is_dir($this->pathStore . '/' . $this->shard->getDriver()))
            mkdir($this->pathStore . '/' . $this->shard->getDriver(), 0777, true);

        match ($this->shard->getDriver()) {
            'pgsql' => $this->skeletonPgsql(),
            'mysql' => $this->skeletonMysql(),
            default => self::printMessage("The function does not work for this '" . $this->shard->getDriver() . "' driver")
        };

    }

    private function skeletonPgsql(): void
    {
        $skeleton = $this->pgsqlDump(
            $this->shard->getHost(),
            $this->shard->getPort(),
            $this->shard->getUsername(),
            $this->shard->getPassword(),
            $this->shard->getDatabase(),
            $this->shard->getSchema()
        );
        if ($skeleton) {
            self::printMessage("A cast of the '" . $this->shard->getSchema()
                . "' schema of the '" . $this->shard->getDatabase()
                . "' database (" . $this->shard->getHost() . ':' . $this->shard->getPort()
                . ") has been successfully made", 32);

            $path = $this->pathStore . '/' . $this->shard->getDriver() . '/' . $this->shard->getSchema();
            if (!is_dir($path)) mkdir($path);

            $fileName = ($this->args['options']['name'] ?? date("Y-m-d_H-i-s")) . '.sql';
            file_put_contents($path . '/' . $fileName, $skeleton);
            self::printMessage("Migration file '{$fileName}' has been successfully created", 32);
        }
    }

    private function skeletonMysql(): void
    {
        self::printMessage("The feature is under development");
    }

    private function pgsqlDump(string $host, string $port, string $user, string $pass, string $dbname, string $schema = 'public'): string|false|null
    {
        $sh = shell_exec(
            "PGPASSWORD='$pass' pg_dump -h'$host' -p'$port' -U'$user' -n'$schema' -Fp --no-owner --no-acl -s '$dbname'"
            . " | sed -e 's/CREATE TABLE /CREATE TABLE IF NOT EXISTS /g' -e '/^--/d'"
        );
        return !is_string($sh) ? $sh : trim($sh);
    }

    private function mysqlDump(string $user, string $pass, string $host, string $port, string $name, string $fileName = null): string|false|null
    {
        return shell_exec(
            "mysqldump -u'$user' -p'$pass' -h'$host' --protocol=TCP -P'$port' " .
            "--skip-opt --single-transaction --tz-utc --no-data --create-options --triggers $name " .
            "| sed 's/^CREATE TABLE /CREATE TABLE IF NOT EXISTS /' " .
            "| sed 's/ AUTO_INCREMENT=[0-9]*\b//' " .
            ((is_null($fileName)) ? "| cat" : "> {$fileName}")
        );
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Db Help", $cl);

        self::printLabel("extra db [args...] --[options...]", $cl);
        self::printMessage("args - command", $cl);
        self::print("skeleton - make an impression of the database", $cl);

        // skeleton
        self::printLabel("skeleton", $cl);
        self::printMessage("options - Additional parameters", $cl);
        self::print("shard - specifies the Aegis key to connect to (default is 'db')", $cl);
        self::print("name - name for the generated template", $cl);
        self::printLabel("env", $cl);

        self::printTitle("Db Help", $cl);
    }

}