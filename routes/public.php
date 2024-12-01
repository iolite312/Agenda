<?php

use App\Application\Router;

$router = Router::getInstance();

$router->get('/', [App\Controllers\HomeController::class, 'index']);
$router->get('/login', [App\Controllers\LoginController::class, 'index']);
$router->post('/login', [App\Controllers\LoginController::class, 'login']);
$router->post('/logout', [App\Controllers\LoginController::class, 'logout']);