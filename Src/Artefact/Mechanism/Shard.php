<?php

namespace Extra\Src\Artefact\Mechanism;

use Extra\Src\Artefact\CDO\CDO;

/**
 * Class Shard
 *
 * `Shard` is a class that represents the necessary details for a database connection.
 * It holds the details such as the driver, host, port, database, username, password,
 * charset and schema.
 *
 * These details can be passed to the PDO instance for connecting to the database.
 *
 * It also provides the `connect()` method to establish the connection with the database and `connection()`
 * which returns an instance of `CDO` representing the connection to the database.
 *
 * @version 3.1
 * @author Flytachi
 */
class Shard
{
    private ?CDO $cdo = null;
    private string $driver;
    private string $host;
    private int $port;

    private string $database;
    private string $username;
    private string $password;
    private ?string $schema;
    private ?string $charset;

    /**
     * @param string $driver
     * @param string $host
     * @param int $port
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string|null $schema
     * @param string|null $charset
     */
    public function __construct(
        string $driver,
        string $host,
        int $port,
        string $database,
        string $username,
        string $password,
        ?string $schema = null,
        ?string $charset = null)
    {
        $this->driver = $driver;
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->schema = $schema;
        $this->charset = $charset;
    }

    public function getDNS(): string
    {
        $DNS = $this->driver
            . ':host=' . $this->host
            . ';port=' . $this->port
            . ';dbname=' . $this->database
            . ';';
        if ($this->charset) {
            if ($this->driver == 'pgsql')
                $DNS .= "options='--client_encoding=" . $this->charset . "';";
            else $DNS .= 'charset=' . $this->charset . ';';
        }
        return $DNS;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function getSchema(): ?string
    {
        return $this->schema;
    }

    public final function connect(): void
    {
        if (is_null($this->cdo)) $this->cdo = new CDO($this, env('DEBUG', false));
    }

    public final function reconnect(): void
    {
        $this->cdo = null;
        $this->connect();
    }

    /**
     * @return CDO
     */
    public function connection(): CDO
    {
        $this->connect();
        return $this->cdo;
    }
}