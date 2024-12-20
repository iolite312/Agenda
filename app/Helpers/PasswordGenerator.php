<?php

namespace App\Helpers;

class PasswordGenerator
{
    public static function hashPassword($password)
    {
        $salt = StringGenerator::generateRandomString();
        $password = password_hash($password . $salt, PASSWORD_BCRYPT, ['cost' => 12]);

        return ['password' => $password, 'salt' => $salt];
    }
}
