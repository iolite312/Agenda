<?php

namespace App\Repositories;

use App\Application\Session;
use App\Enums\ResponseEnum;
use App\Models\User;

class LoginRepository extends DatabaseRepository
{
    private \PDO $pdo;
    public function __construct()
    {
        parent::__construct();
        $this->pdo = $this->getConnection();
    }
    public function login(string $username, string $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :username");
        $stmt->bindParam(':username', $username, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (empty($result)) {
            return ResponseEnum::NOT_FOUND;
        }
        if (password_verify($password . $result["salt"], $result['password'])) {
            Session::set('user', User::fromDatabase($result));
            return ResponseEnum::SUCCESS;
        } else {
            return ResponseEnum::ERROR;
        }
    }
}