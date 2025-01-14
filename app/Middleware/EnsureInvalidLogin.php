<?php

namespace App\Middleware;

use App\Application\Session;
use App\Application\Response;

class EnsureInvalidLogin implements MiddlewareInterface
{
    public function handle(): bool
    {
        if (Session::get('user')) {
            Response::redirect('/');

            return true;
        }

        return false;
    }
}
