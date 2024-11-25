<?php

namespace App\Application;

use App\Repositories\SessionHandlerRepository;

class Session
{
    public static function start(): void
    {
        $handler = new SessionHandlerRepository();
        session_set_save_handler($handler, true);
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
