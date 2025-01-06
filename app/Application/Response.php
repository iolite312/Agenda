<?php

namespace App\Application;

class Response
{
    private int $statusCode = 200;
    private string $content;

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        echo $this->content;
    }

    public static function setHeader(string $name, string $value): void
    {
        header("$name: $value");
    }

    public static function json(array $data)
    {
        header('Content-Type: application/json');

        return json_encode($data);
    }

    public static function redirect(string $url): void
    {
        header("Location: $url");
        http_response_code(302);
        exit;
    }
}
