<?php

namespace Routes;

use App\Application\Router;
$router = new Router;

$router->get('/', function () {
    return 'Welcome to the homepage!';
});

$router->get('/about', function () {
    return 'This is the about page.';
});

$router->post('/contact', function () {
    return 'Form submitted!';
});