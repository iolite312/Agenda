<?php

use App\Application\Router;

$router = Router::getInstance();

$router->get('/', [App\Controllers\HomeController::class, 'index']);