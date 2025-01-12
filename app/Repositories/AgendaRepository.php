<?php

namespace App\Repositories;

use App\Application\Request;
use App\Models\User;
use App\Models\Agenda;
use App\Enums\ResponseEnum;
use App\Models\Appointments;
use App\Enums\AgendaRolesEnum;
use App\Enums\InvitationsStatusEnum;

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

    public function getAgendaUsersById(int $id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT UA.agenda_id, UA.user_id, UA.role, UA.accepted, U.first_name, U.last_name, U.email 
            FROM `user_agenda` AS UA
            JOIN users AS U on U.id = UA.user_id
            WHERE UA.agenda_id = :id'
        );
        $stmt->execute([
            ':id' => $id,
        ]);

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function getAgendaAppointments(Agenda $agenda)
    {
        $date = null;
        $week = Request::getUrlParam('week');
        $year = Request::getUrlParam('year');
        if (isset($week) && isset($year) && $week != null && $year != null) {
            $date = new \DateTime();
            $date->setISODate($year, $week);
        } else {
            $date = new \DateTime()->modify('monday this week');
        }
        $stmt = $this->pdo->prepare(
            'SELECT * FROM `agenda_items` WHERE agenda_id = :id AND start_time > :date'
        );
        $stmt->execute([
            ':id' => $agenda->id,
            ':date' => $date->format('Y-m-d')
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

    public function addUserToAgenda(string $email, AgendaRolesEnum $permission, int $agendaId)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute([
                ':email' => $email,
            ]);

            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $this->pdo->rollBack();

                return ResponseEnum::NOT_FOUND;
            }

            $userId = $user['id'];

            $stmt = $this->pdo->prepare('INSERT INTO user_agenda (user_id, agenda_id, role, accepted) VALUES (:user_id, :agenda_id, :role, :accepted)');
            $stmt->execute([
                ':user_id' => $userId,
                ':agenda_id' => $agendaId,
                ':role' => $permission->value,
                ':accepted' => InvitationsStatusEnum::PENDING->value,
            ]);

            $this->pdo->commit();

            return ['first_name' => $user['first_name'], 'last_name' => $user['last_name'], 'email' => $user['email'], 'role' => $permission->value, 'accepted' => InvitationsStatusEnum::PENDING->value];
        } catch (\Exception $e) {
            $this->pdo->rollBack();

            return ResponseEnum::ERROR;
        }
    }

    public function removeUserFromAgenda(string $email, int $agendaId)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute([
                ':email' => $email,
            ]);

            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $this->pdo->rollBack();

                return ResponseEnum::NOT_FOUND;
            }

            $userId = $user['id'];

            $stmt = $this->pdo->prepare('DELETE FROM user_agenda WHERE user_id = :user_id AND agenda_id = :agenda_id');
            $stmt->execute([
                ':user_id' => $userId,
                ':agenda_id' => $agendaId,
            ]);

            $this->pdo->commit();

            return ResponseEnum::SUCCESS;
        } catch (\Exception $e) {
            $this->pdo->rollBack();

            return ResponseEnum::ERROR;
        }
    }
}
