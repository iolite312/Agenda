<?php

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use App\Socket\Models\AgendaSocket;

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
