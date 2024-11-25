<?php
use App\Models\Agenda;
use App\Repositories\SessionHandlerRepository;
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
// sleep(5);
session_set_save_handler(new SessionHandlerRepository(), true);
$server->run();