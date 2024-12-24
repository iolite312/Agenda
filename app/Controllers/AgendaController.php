<?php

namespace App\Controllers;

use App\Application\Request;
use App\Application\Response;
use App\Application\Session;
use App\Repositories\AgendaRepository;

class AgendaController extends Controller
{
    private AgendaRepository $agendaRepository;

    public function __construct()
    {
        parent::__construct();
        $this->agendaRepository = new AgendaRepository();
    }

    public function index()
    {
        $agendaId = Request::getParam('id');
        $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));

        return $this->pageLoader->setPage('agenda')->render(['page' => 'agenda', 'id' => $agendaId, 'agendas' => $agendas]);
    }
    public function getAgendaAppointments()
    {
        $appointments = [];
        $agendaId = Request::getParam('id');
        $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));
        foreach ($agendas as $key => $value) {
            if ($value->id == $agendaId) {
                $appointments = $this->agendaRepository->getAgendaAppointments($value);
            }
        }
        Response::setHeader('Content-Type', 'application/json');
        return json_encode($appointments);
    }
}
