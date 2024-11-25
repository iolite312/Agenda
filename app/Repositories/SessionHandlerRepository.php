<?php

namespace App\Repositories;

use SessionHandlerInterface;

class SessionHandlerRepository extends DatabaseRepository implements SessionHandlerInterface
{
    private \PDO $pdo;
    private $table;

    public function __construct($table = 'sessions')
    {
        parent::__construct();
        $this->pdo = $this->getConnection();
        $this->table = $table;
    }

    public function open($savePath, $sessionName)
    {
        // Open the connection (if needed)
        return true;
    }

    public function close()
    {
        // Close the connection (if needed)
        return true;
    }

    public function read($sessionId)
    {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id");
        $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result['data'] : '';
    }

    public function write($sessionId, $data)
    {
        // echo $data;
        // $data = preg_split('/\|/', $data);
        // echo json_encode(unserialize($data[1]));
        $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, last_access) VALUES (:id, :data, NOW())");
        $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
        $stmt->bindParam(':data', $data, \PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function destroy($sessionId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
        $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function gc($maxLifetime)
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_access < NOW() - INTERVAL :maxlifetime SECOND");
        $stmt->bindParam(':maxlifetime', $maxLifetime, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}