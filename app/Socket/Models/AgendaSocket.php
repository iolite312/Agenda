<?php

namespace App\Socket\Models;

use App\Enums\ResponseEnum;
use App\Application\Session;
use App\Models\Appointments;
use Ratchet\ConnectionInterface;
use App\Socket\Helpers\SessionHelper;
use App\Repositories\AgendaRepository;
use Ratchet\MessageComponentInterface;

class AgendaSocket implements MessageComponentInterface
{
    protected $clients;
    protected $rooms;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
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
                $from->send(json_encode("Joined room: $room"));
                break;

            case 'leave':
                $room = $data['room'];
                if (isset($this->rooms[$room][$from->resourceId])) {
                    unset($this->rooms[$room][$from->resourceId]);
                    $from->send(json_encode("Left room: $room"));
                }
                break;

            case 'message':
                $room = $data['room'];
                $message = $data['message'];
                if (isset($this->rooms[$room])) {
                    foreach ($this->rooms[$room] as $client) {
                        $client->send("Room $room: $message");
                    }
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
                $from->send(json_encode(['trigger' => 'appointments', 'appointments' => $appointments]));
                break;
            case 'make-appointment':
                $agendaRepository = new AgendaRepository();
                $startTime = new \DateTime($data['start_time']);
                $endTime = new \DateTime($data['end_time']);

                if ($endTime <= $startTime) {
                    $from->send(json_encode(['status' => ResponseEnum::ERROR->value, 'trigger' => 'make-appointment', 'message' => 'End time cannot be before start time']));
                    break;
                }

                $appointment = new Appointments(null, $startTime, $endTime, $data['name'], $data['description'], $data['color'], $data['agenda_id']);
                $result = $agendaRepository->createAppointment($appointment);
                $room = $data['room'];

                if (isset($this->rooms[$room]) && $result === ResponseEnum::SUCCESS) {
                    foreach ($this->rooms[$room] as $client) {
                        $client->send(json_encode(['trigger' => 'update']));
                    }
                }

                $from->send(json_encode(['status' => $result->value, 'trigger' => 'make-appointment']));
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
