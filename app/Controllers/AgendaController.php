<?php

namespace App\Controllers;

use App\Application\Request;
use App\Application\Response;
use App\Application\Session;
use App\Enums\ResponseEnum;
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

    public function createAgenda()
    {
        $agendaName = Request::getPostField('agendaName');
        $agendaDescription = Request::getPostField('agendaDescription');
        $result = $this->agendaRepository->createAgenda($agendaName, $agendaDescription, Session::get('user'));

        if ($result === ResponseEnum::SUCCESS) {
            $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));
            return $this->pageLoader->setPage('home')->render(['page' => 'Home', 'agendas' => $agendas]);
        } else {
            $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));
            return $this->pageLoader->setPage('home')->render(['page' => 'Home', 'agendas' => $agendas, 'errors' => [$result]]);
        }
    }

    public function deleteAgenda()
    {
        $agendaId = Request::getParam('id');
        $result = $this->agendaRepository->deleteAgenda($agendaId);
        if ($result === ResponseEnum::SUCCESS) {
            $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));
            return $this->pageLoader->setPage('home')->render(['page' => 'Home', 'agendas' => $agendas, 'success' => 'Agenda deleted successfully']);
        } else {
            $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));
            return $this->pageLoader->setPage('home')->render(['page' => 'Home', 'agendas' => $agendas, 'errors' => [$result]]);
        }
    }
}
