<?php

namespace App\Controllers;

use App\Application\Request;
use App\Application\Response;
use App\Application\Session;
use App\Helpers\SaveFile;
use App\Repositories\AgendaRepository;
use Ramsey\Uuid\Uuid;

class ProfileController extends Controller
{
    private AgendaRepository $agendaRepository;

    public function __construct()
    {
        parent::__construct();
        $this->agendaRepository = new AgendaRepository();
    }

    public function index()
    {
        $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));
        return $this->pageLoader->setPage('profile')->render(['page' => 'profile', 'agendas' => $agendas]);
    }

    
    private function rerender(array $paramaters = [])
    {
        $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));
        $paramaters['agendas'] = $agendas;
        return $this->pageLoader->setPage('profile')->render($paramaters);
    }
}