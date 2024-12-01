<?php

namespace App\Controllers;

use App\Application\Request;
use App\Application\Response;
use App\Application\Session;
use App\Enums\ResponseEnum;
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
        return $this->pageLoader->setPage('login')->render();
    }
    public function login()
    {
        $username = Request::getPostField('email');
        $password = Request::getPostField('password');
        $result = $this->loginRepository->login($username, $password);
        if ($result === ResponseEnum::SUCCESS) {
            Response::redirect('/');
        } else {
            return $this->rerender(['error' => 'Login failed']);
        }
    }
    public function logout()
    {
        Session::destroy();
        Response::redirect('/');
    }
    private function rerender(array $paramaters = [])
    {
        $this->pageLoader->setPage('login')->render($paramaters);
    }
}