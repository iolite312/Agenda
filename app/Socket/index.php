<?php

use App\Socket\Models\AgendaSocket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require '/app/vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new AgendaSocket()
        )
    ),
    8082
);

$server->run();
