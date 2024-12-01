<?php

namespace App\Controllers;

use App\Enums\ResponseEnum;
use App\Application\Request;
use App\Application\Session;
use App\Application\Response;
use App\Repositories\LoginRepository;

class LoginController extends Controller
{
    private LoginRepository $loginRepository;

    public function __construct()
    {
        parent::__construct();
        $this->loginRepository = new LoginRepository();
    }

    public function index()
    {
        return $this->pageLoader->setLayout('login')->setPage('login')->render(['page' => 'login']);
    }

    public function login()
    {
        $username = Request::getPostField('email');
        $password = Request::getPostField('password');
        $result = $this->loginRepository->login($username, $password);
        if ($result === ResponseEnum::SUCCESS) {
            Response::redirect('/');
        } else {
            return $this->rerender(['error' => 'Incorrect email or password', 'page' => 'login', 'fields' => $_POST]);
        }
    }

    public function logout()
    {
        Session::destroy();
        Response::redirect('/login');
    }

    private function rerender(array $paramaters = [])
    {
        return $this->pageLoader->setLayout('login')->setPage('login')->render($paramaters);
    }
}
