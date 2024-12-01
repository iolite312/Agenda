<?php

namespace App\Controllers;

class HomeController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        return $this->pageLoader->setPage('home')->render();
    }
}