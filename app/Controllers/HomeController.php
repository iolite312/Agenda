<?php

namespace App\Controllers;

use App\Application\Session;
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

        return $this->pageLoader->setPage('home')->render(['page' => 'home', 'agendas' => $agendas]);
    }
}
