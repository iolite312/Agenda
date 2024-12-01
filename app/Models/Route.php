<?php

namespace App\Models;

use App\Application\Request;
use App\Application\Response;
use App\Middleware\MiddlewareInterface;

class Route
{
    public $uri;
    public $method;
    public $callback;
    public $middlewares;

    public function __construct($uri, $method, $callback, ?MiddlewareInterface $middlewares = null)
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->callback = $callback;
        $this->middlewares = $middlewares ? [$middlewares] : [];
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