<?php

namespace App\Repositories;

use App\Enums\ResponseEnum;
use App\Helpers\PasswordGenerator;

class ProfileRepository extends DatabaseRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = $this->getConnection();
    }

    public function updateProfile($profile)
    {
        try {
            if (empty($profile['password'])) {
                $query = $this->pdo->prepare('UPDATE users SET first_name = :firstName, last_name = :lastName, email = :email, profile_picture = :profile_picture WHERE id = :id');
                $query->execute([
                    'firstName' => $profile['firstName'],
                    'lastName' => $profile['lastName'],
                    'email' => $profile['email'],
                    'profile_picture' => $profile['profile_picture'],
                    'id' => $profile['id'],
                ]);
                echo "1";
                return ResponseEnum::SUCCESS;
            }
            $result = PasswordGenerator::hashPassword($profile['password']);
            $profile['password'] = $result['password'];
            $profile['salt'] = $result['salt'];
            $query = $this->pdo->prepare('UPDATE users SET first_name = :firstName, last_name = :lastName, email = :email, password = :password, salt = :salt, profile_picture = :profile_picture WHERE id = :id');
            $query->execute([
                'firstName' => $profile['firstName'],
                'lastName' => $profile['lastName'],
                'email' => $profile['email'],
                'password' => $profile['password'],
                'salt' => $profile['salt'],
                'profile_picture' => $profile['profile_picture'],
                'id' => $profile['id'],
            ]);

            return ResponseEnum::SUCCESS;
        } catch (\Exception $e) {
            echo $e->getMessage();

            return ResponseEnum::ERROR;
        }
    }
}
