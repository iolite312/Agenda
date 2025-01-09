<?php

namespace App\Middleware;

use App\Application\Request;
use App\Application\Session;
use App\Repositories\AgendaRepository;

class EnsureValidRoleAccess implements MiddlewareInterface
{
    private array $allowedRoles;
    private AgendaRepository $agendaRepository;

    public function __construct(array $allowedRoles = [])
    {
        $this->allowedRoles = $allowedRoles;
        $this->agendaRepository = new AgendaRepository();
    }

    public function handle(): bool
    {
        $id = Request::getParam('id');
        $roles = Session::get('user_roles');

        $userRole = array_filter($roles, fn ($role) => array_key_exists($id, $role));

        // This is here to reset the index
        $userRole = array_values($userRole);

        if (empty($this->allowedRoles)) {
            return true;
        }

        return in_array($userRole[0][$id], $this->allowedRoles, true);
    }
}
