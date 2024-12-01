<?php

namespace App\Repositories;

class DatabaseRepository
{
    protected \PDO $connection;

    private string $host;
    private string $port;
    private string $username;
    private string $password;
    private string $database;

    public function __construct()
    {
        $this->host = $_ENV['MYSQL_HOST'] ?: 'mysql';
        $this->port = $_ENV['MYSQL_PORT'] ?: 3306;
        $this->username = 'root';
        $this->password = $_ENV['MYSQL_PASSWORD'];
        $this->database = $_ENV['MYSQL_DATABASE'];
    }

    public function getConnection(): \PDO
    {
        return $this->connectToDatabase();
    }

    private function connectToDatabase(): \PDO
    {
        if (isset($this->connection)) {
            return $this->connection;
        }

        $dsn = "mysql:host=$this->host;port=$this->port;dbname=$this->database";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->connection = new \PDO($dsn, $this->username, $this->password, $options);

        return $this->connection;
    }
}
