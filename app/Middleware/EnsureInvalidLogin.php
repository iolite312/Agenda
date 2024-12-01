<?php

namespace App\Middleware;

use App\Application\Response;
use App\Application\Session;

class EnsureInvalidLogin implements MiddlewareInterface
{
    public function handle(): bool
    {
        if (Session::get('user')) {
            Response::redirect('/');
            return false;
        }
        return true;
    }
}
