<?php

use App\Application\Router;
use App\Middleware\EnsureInvalidLogin;
use App\Middleware\EnsureValidLogin;

$router = Router::getInstance();

$router->middleware(EnsureInvalidLogin::class, function () use ($router) {
    $router->get('/login', [App\Controllers\LoginController::class, 'index']);
    $router->post('/login', [App\Controllers\LoginController::class, 'login']);
    $router->get('/register', [App\Controllers\RegisterController::class, 'index']);
    $router->post('/register', [App\Controllers\RegisterController::class, 'register']);
});
$router->middleware(EnsureValidLogin::class, function () use ($router) {
    $router->get('/', [App\Controllers\HomeController::class, 'index']);
    $router->get('/logout', [App\Controllers\LoginController::class, 'logout']);
});