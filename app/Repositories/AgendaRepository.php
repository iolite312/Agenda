<?php

namespace App\Repositories;

use App\Models\Appointments;
use App\Models\User;
use App\Models\Agenda;

class AgendaRepository extends DatabaseRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = $this->getConnection();
    }

    public function getAgendaById(User $user): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT A.*, UA.*
            FROM `agendas` AS A 
            JOIN user_agenda AS UA ON UA.agenda_id = A.id 
            JOIN users AS U ON U.id = UA.user_id
            WHERE U.id = :id'
        );
        $stmt->execute([
            ':id' => $user->id,
        ]);

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $agendas = [];

        foreach ($result as $key => $value) {
            array_push($agendas, Agenda::fromDatabase($value));
        }

        return $agendas;
    }

    public function getAgendaAppointments(Agenda $agenda)
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM `agenda_items` WHERE agenda_id = :id'
        );
        $stmt->execute([
            ':id' => $agenda->id,
        ]);

        $rawAppointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $appointments = [];
        foreach ($rawAppointments as $raw) {
            $appointment = Appointments::fromDatabase($raw);

            $appointments[] = $appointment;
        }

        return $appointments;
    }
}
