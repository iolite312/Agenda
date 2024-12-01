<?php

namespace App\Models;

use App\Application\Session;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Agenda implements MessageComponentInterface
{
    private $clients;

    // private SessionHandlerRepository $sessionsHandler;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        // Get the PHPSESSID from the handshake headers
        $headers = $conn->httpRequest->getHeaders();
        $cookies = $headers['Cookie'][0] ?? '';
        preg_match('/PHPSESSIDC=([^;]+)/', $cookies, $matches);
        $sessionId = $matches[1] ?? null;
        if ($sessionId) {
            Session::start($sessionId);
            echo json_encode(Session::getAll()) . "\n";
            Session::set("test2", "redis");
            echo json_encode(Session::getAll()) . "\n";
        }
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf(
            'Connection %d sending message "%s" to %d other connection%s' . "\n"
            ,
            $from->resourceId,
            $msg,
            $numRecv,
            $numRecv == 1 ? '' : 's'
        );
        echo Session::get("test2") . "\n";

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}