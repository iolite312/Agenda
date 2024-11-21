<?php

namespace App\Application;

class Router
{
    private array $routes = [];
    public Request $request;
    public Response $response;
    private static Router $instance;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    public function get($uri, $callback)
    {
        $this->routes['GET'][$uri] = $callback;
    }

    public function post($uri, $callback)
    {
        $this->routes['POST'][$uri] = $callback;
    }

    public function resolve(): void
    {
        $uri = $this->request->getPath();
        $method = $this->request->getMethod();
        $callback = $this->routes[$method][$uri] ?? null;

        if ($callback === null) {
            $this->response->setStatusCode(404);
            $this->response->setContent('404 Not Found');
        } else {
            $callback[0] = new $callback[0];
            $content = call_user_func($callback, $this->request, $this->response);
            $this->response->setContent($content);
        }

        $this->response->send();
    }
}