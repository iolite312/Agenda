<?php
use App\Models\Agenda;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require "/app/vendor/autoload.php";

error_reporting(E_ALL & ~E_DEPRECATED);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Agenda()
        )
    ),
    8082
);

$server->run();