<?php

namespace App\Controllers;

use App\Repositories\GetDatabasesRepository;
use App\Application\Session;

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
        Session::set("test", ["test" => "test"]);
        Session::set("test2", "test");
        $databases = $this->getDatabasesRepository->getAllDatabases();
        return $this->pageLoader->setPage('home')->render([
            'databases' => $databases
        ]);
    }
}