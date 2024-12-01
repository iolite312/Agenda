<?php

namespace App\Middleware;

use App\Application\Response;
use App\Application\Session;

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