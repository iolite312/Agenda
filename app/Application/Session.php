<?php

namespace App\Application;

class Session
{
    public static function start(): void
    {
        session_start();
    }

    public static function destroy(): void
    {
        session_destroy();
    }

    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
}
