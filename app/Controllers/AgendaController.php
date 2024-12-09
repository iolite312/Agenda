<?php

namespace App\Controllers;

use App\Application\Request;
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
        $id = Request::getParam('id');
        $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));

        return $this->pageLoader->setPage('agenda')->render(['page' => 'agenda', 'id' => $id, 'agendas' => $agendas]);
    }
}
