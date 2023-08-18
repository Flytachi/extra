<?php

namespace Extra\Src\Artefact;

class Shard
{
    private string $driver;
    private string $host;
    private int $port;

    private string $database;
    private string $username;
    private string $password;
    private string|null $charset;
    private string|null $schema;

    /**
     * @param string $driver
     * @param string $host
     * @param int $port
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string|null $charset
     * @param string|null $schema
     */
    public function __construct(
        string $driver,
        string $host,
        int $port,
        string $database,
        string $username,
        string $password,
        ?string $charset = null,
        ?string $schema = null)
    {
        $this->driver = $driver;
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->charset = $charset;
        $this->schema = $schema;
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
}