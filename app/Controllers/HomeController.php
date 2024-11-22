<?php

namespace App\Controllers;

use App\Repositories\GetDatabasesRepository;

class HomeController extends Controller
{
    private GetDatabasesRepository $getDatabasesRepository;

    public function __construct()
    {
        parent::__construct();
        $this->getDatabasesRepository = new GetDatabasesRepository();
    }
    public function index()
    {
        $databases = $this->getDatabasesRepository->getAllDatabases();
        return $this->pageLoader->setPage('home')->render([
            'databases' => $databases
        ]);
    }
}