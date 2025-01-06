<?php

use App\Application\Router;
use App\Middleware\EnsureValidLogin;
use App\Middleware\EnsureInvalidLogin;
use App\Middleware\EnsureValidAgendaAccess;

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
    $router->post('/agenda/create', [App\Controllers\AgendaController::class, 'createAgenda']);
    $router->middleware(EnsureValidAgendaAccess::class, function () use ($router) {
        $router->get('/agenda/{id}', [App\Controllers\AgendaController::class, 'index']);
        $router->get('/agenda/{id}/edit', [App\Controllers\EditAgendaController::class, 'index']);
        $router->get('/agenda/{id}/edit/users', [App\Controllers\EditAgendaController::class, 'getAgendaUsers']);
        $router->post('/agenda/{id}/edit/adduser', [App\Controllers\EditAgendaController::class, 'addUser']);
        $router->post('/agenda/{id}/edit/removeuser', [App\Controllers\EditAgendaController::class, 'removeUser']);
        $router->post('/agenda/{id}/delete', [App\Controllers\AgendaController::class, 'deleteAgenda']);
        $router->get('/api/agenda/{id}/appointments', [App\Controllers\AgendaController::class, 'getAgendaAppointments']);
    });
});
