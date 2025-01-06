<?php

namespace App\Repositories;

use App\Enums\AgendaRolesEnum;
use App\Enums\InvitationsStatusEnum;
use App\Enums\ResponseEnum;
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

    public function getAgendaByUserId(User $user): array
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

    public function createAgenda(string $name, string $description, User $user)
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare('INSERT INTO agendas (name, description, default_color) VALUES (:name, :description, :default_color)');
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':default_color' => '#0a58ca',
            ]);

            $agendaId = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare('INSERT INTO user_agenda (user_id, agenda_id, role, accepted) VALUES (:user_id, :agenda_id, :role, :accepted)');
            $stmt->execute([
                ':user_id' => $user->id,
                ':agenda_id' => $agendaId,
                ':role' => AgendaRolesEnum::ADMIN->value,
                ':accepted' => InvitationsStatusEnum::ACCEPTED->value,
            ]);

            $this->pdo->commit();

            return ResponseEnum::SUCCESS;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return $e->getMessage();
        }
    }

    public function deleteAgenda($id)
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare('DELETE FROM agenda_items WHERE agenda_id = :id');
            $stmt->execute([
                ':id' => $id,
            ]);
            $stmt = $this->pdo->prepare('DELETE FROM user_agenda WHERE agenda_id = :id');
            $stmt->execute([
                ':id' => $id,
            ]);
            $stmt = $this->pdo->prepare(
                'DELETE FROM `agendas` WHERE id = :id'
            );
            $stmt->execute([
                ':id' => $id,
            ]);

            $this->pdo->commit();
            return ResponseEnum::SUCCESS;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return $e->getMessage();
        }
    }
}
