<?php

namespace App\Middleware;

use App\Application\Session;
use App\Application\Response;

class EnsureValidLogin implements MiddlewareInterface
{
    public function handle(): bool
    {
        if (!Session::get('user')) {
            Response::redirect('/login');

            return false;
        }

        return true;
    }
}
