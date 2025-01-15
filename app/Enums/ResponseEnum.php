<?php

namespace App\Enums;

enum ResponseEnum: string
{
    case UNKOWN = 'unkown';
    case NOT_FOUND = 'not_found';
    case ERROR = 'error';
    case ALREADY_EXISTS = 'already_exists';
    case SUCCESS = 'success';
}
