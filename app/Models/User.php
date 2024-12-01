<?php

namespace App\Models;

class User
{
    public $id;
    public $firstName;
    public $lastName;
    public $email;
    public $profilePicture;

    public $fullName {
        get => "{$this->firstName} {$this->lastName}";
    }

    public function __construct($id, $firstName, $lastName, $email, $profilePicture)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->profilePicture = $profilePicture;
    }
    public static function fromDatabase(array $user): self
    {
        return new self(
            $user['id'],
            $user['first_name'],
            $user['last_name'],
            $user['email'],
            $user['profile_picture']
        );
    }
}