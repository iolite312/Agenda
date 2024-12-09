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

    public function saveProfile()
    {
        $user = Session::get('user');
        $avatarData = Request::getPostField('avatarData');
        $firstName = Request::getPostField('firstName');
        $lastName = Request::getPostField('lastName');
        $email = Request::getPostField('email');
        $password = Request::getPostField('password');
        $confirmPassword = Request::getPostField('confirmPassword');

        $base64String = $avatarData;
        $data = explode(',', $base64String);
        // die();
        if (count($data) === 2 && preg_match('/^data:image\/(\w+);base64/', $data[0], $matches) === 1) {
            $imageType = $matches[1];
            $allowedTypes = ['png', 'jpeg', 'jpg', 'gif'];

            if (in_array($imageType, $allowedTypes)) {
                $imageData = base64_decode($data[1]);
                $newAvatarName = Uuid::uuid4()->toString() . '.' . $imageType;
                $uploadDir = '/app/public/assets/images/uploads/';
                $destination = $uploadDir . $newAvatarName;

                if (file_put_contents($destination, $imageData)) {
                    echo $newAvatarName;
                    // return ["type" => ResponseEnum::SUCCESS, "name" => $newAvatarName];
                } else {
                    echo "Failed to save image.";
                }
            } else {
                return "Invalid base64 image type.";
            }
        } else {
            var_dump($matches);
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