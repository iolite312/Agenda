<?php

namespace App\Middleware;

use App\Application\Request;
use App\Application\Response;
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
        if (empty(Session::get('user'))) {
            Response::redirect('/');

            return true;
        }
        $agendas = $this->agendaRepository->getAgendaByUserId(Session::get('user'));

        foreach ($agendas as $key => $value) {
            if ($value->id == Request::getParam('id')) {
                return false;
            }
        }

        return true;
    }
}
