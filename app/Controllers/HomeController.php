<?php

namespace App\Controllers;

use App\Application\Session;
use App\Enums\AgendaRolesEnum;
use App\Repositories\AgendaRepository;

class HomeController extends Controller
{
    private AgendaRepository $agendaRepository;

    public function __construct()
    {
        parent::__construct();
        $this->agendaRepository = new AgendaRepository();
    }

    public function index()
    {
        $agendas = $this->agendaRepository->getAgendaByUserId(Session::get('user'));
        $roles = [];

        foreach ($agendas as $key => $value) {
            // if (!$value->personal_agenda) {
            $accessLevel = $this->agendaRepository->getAgendaUsersById($value->id);

            $accessLevel = array_filter($accessLevel, fn ($user) => $user['user_id'] === Session::get('user')->id);

            foreach ($accessLevel as $key => $value) {
                $role = [$value['agenda_id'] => AgendaRolesEnum::from($value['role'])];
                array_push($roles, $role);
                // }
            }
        }
        Session::set('user_roles', $roles);

        return $this->pageLoader->setPage('home')->render(['page' => 'home', 'agendas' => $agendas]);
    }
}
