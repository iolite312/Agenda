<?php

namespace App\Models;

use App\Middleware\MiddlewareInterface;

class Route
{
    public $uri;
    public $method;
    public $callback;
    public $middlewares;
    public $parameters = [];

    public function __construct($uri, $method, $callback, ?MiddlewareInterface $middlewares = null)
    {
        $this->method = $method;
        $this->callback = $callback;
        $this->middlewares = $middlewares ? [$middlewares] : [];
        $this->parseUri($uri);
    }

    private function parseUri($uri)
    {
        // Replace {parameter} with named regular expression groups
        $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) {
            $this->parameters[] = $matches[1];
            return '([a-zA-Z0-9_-]+)'; // Match typical parameter formats
        }, $uri);

        $this->uri = "#^" . $pattern . "$#"; // Complete the regex for full URI matching
    }

    public function match($uri): bool
    {
        return preg_match($this->uri, $uri) === 1;
    }

    public function extractParameters($uri)
    {
        $matches = [];
        preg_match($this->uri, $uri, $matches);
        array_shift($matches); // Remove the full match
        return array_combine($this->parameters, $matches);
    }

    public function activateMiddleware()
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware->handle()) {
                return false;
            }
        }

        return true;
    }
}
