<?php

namespace App\Repositories;

use App\Application\Session;
use App\Enums\AgendaRolesEnum;
use App\Enums\InvitationsStatusEnum;
use App\Enums\ResponseEnum;
use App\Helpers\StringGenerator;
use App\Models\User;

class RegisterRepository extends DatabaseRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = $this->getConnection();
    }

    public function register(string $firstName, string $lastName, string $email, string $password)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO users (first_name, last_name, email, password, salt, profile_picture) VALUES (:first_name, :last_name, :email, :password, :salt, :profile_picture)");
            $salt = StringGenerator::generateRandomString();
            $password = password_hash($password . $salt, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':password' => $password,
                ':salt' => $salt,
                ':profile_picture' => 'placeholder.jpg'
            ]);

            $userId = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("INSERT INTO agendas (name, default_color) VALUES (:name, :default_color)");
            $stmt->execute([
                ':name' => "{$firstName} {$lastName}",
                ':default_color' => "#0a58ca",
            ]);

            $agendaId = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("INSERT INTO user_agenda (user_id, agenda_id, role, accepted) VALUES (:user_id, :agenda_id, :role, :accepted)");
            $stmt->execute([
                ':user_id' => $userId,
                ':agenda_id' => $agendaId,
                ':role' => AgendaRolesEnum::ADMIN->value,
                ':accepted' => InvitationsStatusEnum::ACCEPTED->value,
            ]);

            $this->pdo->commit();
            $user = new User($userId, $firstName, $lastName, $email, 'placeholder.jpg');
            Session::set('user', $user);
            return ResponseEnum::SUCCESS;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            echo "Error: " . $e->getMessage();
            return ResponseEnum::ERROR;
        }

    }
}