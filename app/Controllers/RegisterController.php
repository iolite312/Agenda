<?php

namespace App\Controllers;

use App\Enums\ResponseEnum;
use App\Application\Request;
use App\Application\Response;
use App\Repositories\RegisterRepository;

class RegisterController extends Controller
{
    private RegisterRepository $registerRepository;

    public function __construct()
    {
        parent::__construct();
        $this->registerRepository = new RegisterRepository();
    }

    public function index()
    {
        return $this->pageLoader->setLayout('login')->setPage('register')->render(['page' => 'register']);
    }

    public function register()
    {
        $firstName = Request::getPostField('firstName');
        $lastName = Request::getPostField('lastName');
        $email = Request::getPostField('email');
        $password = Request::getPostField('password');
        $confirmPassword = Request::getPostField('confirmPassword');

        if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
            return $this->rerender(['error' => 'All fields are required', 'page' => 'register', 'fields' => $_POST]);
        }

        if ($password !== $confirmPassword) {
            return $this->rerender(['error' => 'Passwords do not match', 'page' => 'register', 'fields' => $_POST]);
        }

        $result = $this->registerRepository->register($firstName, $lastName, $email, $password);
        if ($result === ResponseEnum::SUCCESS) {
            Response::redirect('/');
        } else {
            return $this->rerender(['error' => 'Something went wrong', 'page' => 'register', 'fields' => $_POST]);
        }
    }

    public function rerender(array $data)
    {
        return $this->pageLoader->setLayout('login')->setPage('register')->render($data);
    }
}
