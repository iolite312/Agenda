<?php

namespace App\Application;

use Ramsey\Uuid\Uuid;
use App\Repositories\SessionHandlerRepository;

class Session
{
    private static ?string $sessionId = null;
    private static array $data = [];
    private static SessionHandlerRepository $handler;

    public static function start(?string $sessionId = null): void
    {
        self::$handler = new SessionHandlerRepository();
        self::$sessionId = $sessionId;
        // Check if the session cookie exists
        if (isset($_COOKIE['PHPSESSIDC']) || !is_null(self::$sessionId)) {
            if (isset($_COOKIE['PHPSESSIDC'])) {
                self::$sessionId = $_COOKIE['PHPSESSIDC'];
            }
            self::$data = self::$handler->read(self::$sessionId) ?: [];
        } else {
            // Generate a new session ID if no cookie is set
            self::$sessionId = Uuid::uuid4()->toString();
            self::$handler->writeNewSession(self::$sessionId);
            setcookie('PHPSESSIDC', self::$sessionId, time() + 3600, '/');
        }
    }

    public static function destroy(): void
    {
        if (self::$sessionId) {
            // Clear session data
            self::$handler->destroy(self::$sessionId);
            setcookie('PHPSESSIDC', '', time() - 3600, '/');
            self::$sessionId = null;
            self::$data = [];
        }
    }

    public static function get(string $key): mixed
    {
        return self::$data[$key] ?? null;
    }

    public static function getAll(): array
    {
        return self::$data;
    }

    public static function set(string $key, mixed $value): void
    {
        self::$data[$key] = $value;
        if (self::$sessionId) {
            // Persist session data using the handler
            self::$handler->write(self::$sessionId, self::$data);
        }
    }
}
