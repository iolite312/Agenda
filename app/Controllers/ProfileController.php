<?php

namespace App\Controllers;

use App\Helpers\SaveFile;
use App\Enums\ResponseEnum;
use App\Application\Request;
use App\Application\Session;
use App\Repositories\AgendaRepository;
use App\Repositories\ProfileRepository;

class ProfileController extends Controller
{
    private AgendaRepository $agendaRepository;
    private ProfileRepository $profileRepository;

    public function __construct()
    {
        parent::__construct();
        $this->agendaRepository = new AgendaRepository();
        $this->profileRepository = new ProfileRepository();
    }

    public function index()
    {
        $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));

        return $this->pageLoader->setPage('profile')->render(['page' => 'profile', 'agendas' => $agendas]);
    }

    public function saveProfile()
    {
        $user = Session::get('user');
        $avatarData = Request::getPostField('avatarData');
        $firstName = Request::getPostField('firstName');
        $lastName = Request::getPostField('lastName');
        $email = Request::getPostField('email');
        $password = Request::getPostField('password');
        $confirmPassword = Request::getPostField('confirmPassword');

        if (!is_null($password) && $password !== $confirmPassword) {
            return $this->rerender(['error' => 'Passwords do not match', 'page' => 'Profile', 'fields' => $_POST]);
        }

        $result = SaveFile::saveFile($avatarData);
        if ($result['type'] === ResponseEnum::SUCCESS) {
            SaveFile::deleteFile($user->profilePicture);
            $user->profilePicture = $result['name'];
        } else {
            return $this->rerender(['error' => $result['Error'], 'page' => 'profile', 'fields' => $_POST]);
        }

        $result = $this->profileRepository->updateProfile([
            'id' => $user->id,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'profile_picture' => $user->profilePicture,
            'password' => $password,
        ]);

        if ($result === ResponseEnum::SUCCESS) {
            $user->firstName = $firstName;
            $user->lastName = $lastName;
            $user->email = $email;
            Session::set('user', $user);
        } else {
            return $this->rerender(['error' => 'Something went wrong', 'page' => 'profile', 'fields' => $_POST]);
        }

        return $this->rerender(['page' => 'profile']);
    }

    private function rerender(array $paramaters = [])
    {
        $agendas = $this->agendaRepository->getAgendaById(Session::get('user'));
        $paramaters['agendas'] = $agendas;

        return $this->pageLoader->setPage('profile')->render($paramaters);
    }
}
