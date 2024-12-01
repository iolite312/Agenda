<?php

namespace App\Application;

use App\Models\Route;

class Router
{
    private array $routes = [];
    private mixed $middlewares;
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
        $this->routes[] = $this->constructRoute($uri, 'GET', $callback);
    }

    public function post($uri, $callback)
    {
        $this->routes[] = $this->constructRoute($uri, 'POST', $callback);
    }

    public function middleware(mixed $middlewareClass, callable $routesGroup)
    {
        $this->middlewares = new $middlewareClass();
        $routesGroup();
        $this->middlewares = null;
    }

    public function resolve(): void
    {
        // Initialize the session
        \App\Application\Session::start();
        $uri = $this->request->getPath();
        $method = $this->request->getMethod();
        $route = $this->routeExists($uri, $method);

        if (is_null($route) || $route->activateMiddleware()) {
            $this->response->setStatusCode(404);
            $this->response->setContent('404 Not Found');
        } else {
            $route->callback[0] = new $route->callback[0];
            $content = call_user_func($route->callback, $this->request, $this->response);
            $this->response->setContent($content);
        }
        $this->response->send();
    }

    private function routeExists($uri, $method): Route|null
    {
        $routes = array_filter($this->routes, function ($route) use ($uri, $method) {
            return $route->uri === $uri && $route->method === $method;
        });
        return !empty($routes) ? current($routes) : null;
    }

    private function constructRoute($uri, $method, $callback)
    {
        return new Route($uri, $method, $callback, $this->middlewares);
    }

}
