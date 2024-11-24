<?php

use App\Models\Agenda;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
require "../vendor/autoload.php";

$app = App\Application\Application::getInstance();
$router = App\Application\Router::getInstance();

// Load all routes
$handle = opendir(__DIR__ . '/../routes');
while (false !== ($file = readdir($handle))) {
    if ($file == '.' || $file == '..') {
        continue;
    }
    require_once __DIR__ . '/../routes/' . $file;
}
closedir($handle);
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Agenda()
        )
    ),
    8082
);
$server->run();
$app->run();