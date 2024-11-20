<?php

namespace App\Application;

class Router
{
    protected $routes = [];

    public function get($uri, $callback)
    {
        self::$routes['GET'][$uri] = $callback;
    }

    public function post($uri, $callback)
    {
        self::$routes['POST'][$uri] = $callback;
    }

    public function resolve($method, $uri)
    {
        $uri = rtrim($uri, '/'); // Normalize URI (remove trailing slashes)
        $callback = self::$routes[$method][$uri] ?? null;

        if (!$callback) {
            // http_response_code(404);
            return "404 Not Found";
        }

        if (is_callable($callback)) {
            return call_user_func($callback);
        }

        if (is_string($callback)) {
            return $callback;
        }

        throw new \Exception('Invalid route callback.');
    }
}