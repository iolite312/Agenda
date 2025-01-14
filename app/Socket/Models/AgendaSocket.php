<?php

namespace App\Socket\Models;

use App\Application\Session;
use App\Repositories\AgendaRepository;
use App\Socket\Helpers\SessionHelper;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class AgendaSocket implements MessageComponentInterface
{
    protected $clients;
    protected $rooms;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->rooms = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Get the PHPSESSID from the handshake headers
        $headers = $conn->httpRequest->getHeaders();
        $cookies = $headers['Cookie'][0] ?? '';
        preg_match('/PHPSESSIDC=([^;]+)/', $cookies, $matches);
        $sessionId = $matches[1] ?? null;
        if ($sessionId) {
            $this->clients->attach($conn, $sessionId);

            echo "New connection! ({$conn->resourceId})\n";
        } else {
            $conn->close();
        }

    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        switch ($data['action']) {
            case 'join':
                $room = $data['room'];
                if (!isset($this->rooms[$room])) {
                    $this->rooms[$room] = [];
                }
                $this->rooms[$room][$from->resourceId] = $from;
                $from->send("Joined room: $room");
                break;

            case 'leave':
                $room = $data['room'];
                if (isset($this->rooms[$room][$from->resourceId])) {
                    unset($this->rooms[$room][$from->resourceId]);
                    $from->send("Left room: $room");
                }
                break;

            case 'message':
                $room = $data['room'];
                $message = $data['message'];
                if (isset($this->rooms[$room])) {
                    foreach ($this->rooms[$room] as $client) {
                        $client->send("Room $room: $message");
                        // if ($from !== $client) { // Don't send the message to the sender
                        // }
                    }
                }
                break;
            case 'dump':
                foreach ($this->clients as $client) {
                    $clientSession = $this->clients->offsetGet($client);
                    $client->send("Client {$client->resourceId} session: " . json_encode($clientSession));
                }
                break;
            case 'appointments':
                $agendaRepository = new AgendaRepository();
                $appointments = [];
                $agendaId = $data['id'];
                $week = $data['week'];
                $year = $data['year'];
                $ses = SessionHelper::getSessionId($from->resourceId, $this->clients);
                Session::start($ses);
                $agendas = $agendaRepository->getAgendaByUserId(Session::get('user'));
                foreach ($agendas as $key => $value) {
                    if ($value->id == $agendaId) {
                        $appointments = $agendaRepository->getAgendaAppointments($value, $week, $year);
                    }
                }
                $from->send(json_encode($appointments));
                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        // Remove the client from all rooms
        foreach ($this->rooms as $room => &$clients) {
            if (isset($clients[$conn->resourceId])) {
                unset($clients[$conn->resourceId]);
            }
        }
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
