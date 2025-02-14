<?php

namespace App\Repositories;

use App\Application\Request;
use App\Application\Response;
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

    public function getAgendaAppointments(Agenda $agenda, int $week = null, int $year = null)
    {
        $date = null;
        if (isset($week) && isset($year) && $week != null && $year != null) {
            $date = new \DateTime();
            $date->setISODate($year, $week);
        } else {
            $date = new \DateTime()->modify('monday this week');
        }

        $startDate = $date->format('Y-m-d');
        $endDate = $date->modify('+2 weeks')->format('Y-m-d');

        $query = '
        SELECT * FROM `agenda_items` 
            WHERE agenda_id = :id 
            AND (
                (start_time >= :start_date1 AND start_time <= :end_date1) 
                OR (end_time >= :start_date2 AND end_time <= :end_date2) 
                OR (start_time <= :start_date3 AND end_time >= :end_date3)
            )
        ';

        $stmt = $this->pdo->prepare($query);

        // I have to it this way because PDO driver refuses to replace all the instances of the same paramater except for the first one
        $stmt->bindValue(':id', $agenda->id, \PDO::PARAM_INT);
        $stmt->bindValue(':start_date1', $startDate, \PDO::PARAM_STR);
        $stmt->bindValue(':end_date1', $endDate, \PDO::PARAM_STR);
        $stmt->bindValue(':start_date2', $startDate, \PDO::PARAM_STR);
        $stmt->bindValue(':end_date2', $endDate, \PDO::PARAM_STR);
        $stmt->bindValue(':start_date3', $startDate, \PDO::PARAM_STR);
        $stmt->bindValue(':end_date3', $endDate, \PDO::PARAM_STR);

        $stmt->execute();

        $rawAppointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $appointments = [];
        foreach ($rawAppointments as $raw) {
            $appointment = Appointments::fromDatabase($raw);

            $appointments[] = $appointment;
        }

        return $appointments;
    }

    public function createAppointment(Appointments $appointment)
    {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO agenda_items (start_time, end_time, name, description, color, agenda_id) VALUES (:start_time, :end_time, :name, :description, :color, :agenda_id)');
            $stmt->execute([
                ':start_time' => $appointment->start_time->format('Y-m-d H:i:s'),
                ':end_time' => $appointment->end_time->format('Y-m-d H:i:s'),
                ':name' => $appointment->name,
                ':description' => $appointment->description,
                ':color' => $appointment->color,
                ':agenda_id' => $appointment->agenda_id,
            ]);

            return ResponseEnum::SUCCESS;
        } catch (\Exception $e) {
            return ResponseEnum::ERROR;
        }
    }

    public function updateAppointment(Appointments $appointment)
    {
        try {
            $stmt = $this->pdo->prepare('UPDATE agenda_items SET start_time = :start_time, end_time = :end_time, name = :name, description = :description, color = :color WHERE id = :id');
            $stmt->execute([
                ':start_time' => $appointment->start_time->format('Y-m-d H:i:s'),
                ':end_time' => $appointment->end_time->format('Y-m-d H:i:s'),
                ':name' => $appointment->name,
                ':description' => $appointment->description,
                ':color' => $appointment->color,
                ':id' => $appointment->id,
            ]);

            return ResponseEnum::SUCCESS;
        } catch (\Exception $e) {
            return ResponseEnum::ERROR;
        }
    }

    public function deleteAppointment(int $id)
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM agenda_items WHERE id = :id');
            $stmt->execute([
                ':id' => $id,
            ]);

            return ResponseEnum::SUCCESS;
        } catch (\Exception $e) {
            return ResponseEnum::ERROR;
        }
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

    public function changeAgendaName(int $id, string $name, string $description)
    {
        try {
            $stmt = $this->pdo->prepare('UPDATE agendas SET name = :name, description = :description WHERE id = :id');
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':id' => $id,
            ]);

            return ResponseEnum::SUCCESS;
        } catch (\Exception $e) {
            return ResponseEnum::ERROR;
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

    public function getInvitationStatus(User $user, int $agendaId)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM user_agenda WHERE user_id = :user_id AND agenda_id = :agenda_id AND accepted = :accepted');
            $stmt->execute([
                ':user_id' => $user->id,
                ':agenda_id' => $agendaId,
                ':accepted' => InvitationsStatusEnum::PENDING->value,
            ]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$result) {
                return null;
            }

            return InvitationsStatusEnum::from($result['accepted']);
        } catch (\Exception $e) {
            return ResponseEnum::ERROR;
        }
    }

    public function updateInvitationStatus(User $user, int $agendaId, InvitationsStatusEnum $status)
    {
        try {
            if ($status === InvitationsStatusEnum::DECLINED) {
                $stmt = $this->pdo->prepare('DELETE FROM user_agenda WHERE user_id = :user_id AND agenda_id = :agenda_id');
                $stmt->execute([
                    ':user_id' => $user->id,
                    ':agenda_id' => $agendaId,
                ]);
            } else {
                $stmt = $this->pdo->prepare('UPDATE user_agenda SET accepted = :status WHERE user_id = :user_id AND agenda_id = :agenda_id');
                $stmt->execute([
                    ':status' => $status->value,
                    ':user_id' => $user->id,
                    ':agenda_id' => $agendaId,
                ]);
            }
            return ResponseEnum::SUCCESS;
        } catch (\Exception $e) {
            return ResponseEnum::ERROR;
        }

    }

    public function editUserPermission(string $email, AgendaRolesEnum $permission, int $agendaId)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute([
                ':email' => $email,
            ]);

            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $this->pdo->rollBack(); // Rollback the transaction if the user is not found

                return ResponseEnum::NOT_FOUND;
            }

            $userId = $user['id'];

            $stmt = $this->pdo->prepare('UPDATE user_agenda SET role = :role WHERE user_id = :user_id AND agenda_id = :agenda_id');
            $stmt->execute([
                ':role' => $permission->value,
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
