<?php

use App\Application\Router;
use App\Middleware\EnsureValidLogin;
use App\Middleware\EnsureInvalidLogin;

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
    $router->get('/profile', [App\Controllers\ProfileController::class, 'index']);
    $router->post('/profile', [App\Controllers\ProfileController::class, 'saveProfile']);
    $router->get('/agenda/{id}', [App\Controllers\AgendaController::class, 'index']);
});
