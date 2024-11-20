<?php 
require "../vendor/autoload.php";

use app\Application\Router;
$router = new Router();
// Get the request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Resolve the route and send the response
$response = $router->resolve($method, $_SERVER['REQUEST_URI']);

echo $response;
