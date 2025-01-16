<?php

namespace App\Controllers;

use App\Enums\ResponseEnum;
use App\Application\Request;
use App\Application\Session;
use App\Application\Response;
use App\Enums\AgendaRolesEnum;
use App\Repositories\AgendaRepository;

class EditAgendaController extends Controller
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

        return $this->pageLoader->setPage('editAgenda')->render(['page' => 'edit', 'id' => $agendaId, 'agendas' => $agendas]);
    }

    public function getAgendaUsers()
    {
        $result = $this->agendaRepository->getAgendaUsersById(Request::getParam('id'));

        return Response::json(['status' => 'success', 'data' => $result]);
    }

    public function changeAgendaName()
    {
        $agendaId = Request::getParam('id');
        $agendaName = Request::getPostField('agendaName');
        $agendaDescription = Request::getPostField('agendaDescription');
        $result = $this->agendaRepository->changeAgendaName($agendaId, $agendaName, $agendaDescription);

        if ($result === ResponseEnum::SUCCESS) {
            $agendas = $this->agendaRepository->getAgendaByUserId(Session::get('user'));

            Response::redirect("/agenda/$agendaId/edit");
        } else {
            $agendas = $this->agendaRepository->getAgendaByUserId(Session::get('user'));

            return $this->pageLoader->setPage('home')->render(['page' => 'Home', 'agendas' => $agendas, 'errors' => [$result]]);
        }
    }

    public function addUser()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $email = $data->email;
        $permission = AgendaRolesEnum::from($data->permission);
        $result = $this->agendaRepository->addUserToAgenda($email, $permission, Request::getParam('id'));
        if ($result === ResponseEnum::NOT_FOUND) {
            return Response::json(['status' => 'unknown', 'message' => 'User not found']);
        }
        if ($result === ResponseEnum::ERROR) {
            return Response::json(['status' => 'error', 'message' => 'Something went wrong']);
        }

        return Response::json(['status' => 'success', 'message' => 'User added successfully', 'data' => $result]);
    }

    public function removeUser()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $email = $data->email;
        $result = $this->agendaRepository->removeUserFromAgenda($email, Request::getParam('id'));
        if ($result === ResponseEnum::NOT_FOUND) {
            return Response::json(['status' => 'unknown', 'message' => 'User not found']);
        }
        if ($result === ResponseEnum::ERROR) {
            return Response::json(['status' => 'error', 'message' => 'Something went wrong']);
        }

        return Response::json(['status' => 'success', 'message' => 'User removed successfully']);
    }
}
