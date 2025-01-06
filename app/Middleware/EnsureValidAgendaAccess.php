<?php

namespace App\Middleware;

use App\Application\Request;
use App\Application\Session;
use App\Repositories\AgendaRepository;

class EnsureValidAgendaAccess implements MiddlewareInterface
{
    private AgendaRepository $agendaRepository;

    public function __construct()
    {
        $this->agendaRepository = new AgendaRepository();
    }

    public function handle(): bool
    {
        $agendas = $this->agendaRepository->getAgendaByUserId(Session::get('user'));

        foreach ($agendas as $key => $value) {
            if ($value->id == Request::getParam('id')) {
                return true;
            }
        }

        return false;
    }
}
