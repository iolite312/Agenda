<?php

namespace App\Repositories;

class SessionHandlerRepository extends DatabaseRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = $this->getConnection();
    }

    public function read($sessionId)
    {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id");
        $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? unserialize($result['data']) : '';
    }

    public function write($sessionId, $data)
    {
        $data = serialize($data);
        $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, last_access) VALUES (:id, :data, NOW())");
        $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
        $stmt->bindParam(':data', $data, \PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function writeNewSession($sessionId)
    {
        $stmt = $this->pdo->prepare('INSERT INTO sessions (id, last_access) VALUES (:id, NOW())');
        $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
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