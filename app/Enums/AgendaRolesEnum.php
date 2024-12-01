<?php

namespace App\Enums;

enum AgendaRolesEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case GUEST = 'guest';
}
