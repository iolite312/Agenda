<?php

namespace App\Models;

use App\Repositories\SessionHandlerRepository;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Agenda implements MessageComponentInterface
{
    private $clients;

    // private SessionHandlerRepository $sessionsHandler;

    public function __construct()
    {
        // $this->sessionsHandler = new SessionHandlerRepository();
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        // Get the PHPSESSID from the handshake headers
        $headers = $conn->httpRequest->getHeaders();
        $cookies = $headers['Cookie'][0] ?? '';
        preg_match('/PHPSESSID=([^;]+)/', $cookies, $matches);
        $sessionId = $matches[1] ?? null;

        if ($sessionId) {
            // session_destroy();
            session_id($sessionId);
            session_start();

            // var_dump(json_encode($sessionId));
            var_dump(json_encode($_SESSION['test']));
            // if ($sessionData) {
            //     $sessionData = unserialize($sessionData);
            //     var_dump($sessionData);
            // }
            $_SESSION['test'] = 'redis';
            sleep(10);
            session_write_close();
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