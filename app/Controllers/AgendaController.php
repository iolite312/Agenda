<?php

namespace App\Controllers;

use App\Enums\ResponseEnum;
use App\Application\Request;
use App\Application\Session;
use App\Application\Response;
use App\Enums\InvitationsStatusEnum;
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
        $agendas = $this->agendaRepository->getAgendaByUserId(Session::get('user'));
        $inviteStatus = $this->agendaRepository->getInvitationStatus(Session::get('user'), $agendaId);
        if ($inviteStatus == ResponseEnum::ERROR || empty($inviteStatus)) {
            $inviteStatus = null;
        }

        return $this->pageLoader->setPage('agenda')->render(['page' => 'agenda', 'id' => $agendaId, 'agendas' => $agendas, 'inviteStatus' => $inviteStatus->value]);
    }

    public function createAgenda()
    {
        $agendaName = Request::getPostField('agendaName');
        $agendaDescription = Request::getPostField('agendaDescription');
        $result = $this->agendaRepository->createAgenda($agendaName, $agendaDescription, Session::get('user'));

        if ($result === ResponseEnum::SUCCESS) {
            $agendas = $this->agendaRepository->getAgendaByUserId(Session::get('user'));

            return $this->pageLoader->setPage('home')->render(['page' => 'Home', 'agendas' => $agendas]);
        } else {
            $agendas = $this->agendaRepository->getAgendaByUserId(Session::get('user'));

            return $this->pageLoader->setPage('home')->render(['page' => 'Home', 'agendas' => $agendas, 'errors' => [$result]]);
        }
    }

    public function updateInvitationStatus()
    {
        $agendaId = Request::getParam('id');
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $status = InvitationsStatusEnum::from($data->status);
        $this->agendaRepository->updateInvitationStatus(Session::get('user'), $agendaId, $status);

        Response::redirect('/');
    }

    public function deleteAgenda()
    {
        $agendaId = Request::getParam('id');
        $result = $this->agendaRepository->deleteAgenda($agendaId);
        if ($result === ResponseEnum::SUCCESS) {
            $agendas = $this->agendaRepository->getAgendaByUserId(Session::get('user'));

            return $this->pageLoader->setPage('home')->render(['page' => 'Home', 'agendas' => $agendas, 'success' => 'Agenda deleted successfully']);
        } else {
            $agendas = $this->agendaRepository->getAgendaByUserId(Session::get('user'));

            return $this->pageLoader->setPage('home')->render(['page' => 'Home', 'agendas' => $agendas, 'errors' => [$result]]);
        }
    }
}
