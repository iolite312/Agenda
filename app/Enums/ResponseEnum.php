<?php

namespace App\Enums;

enum ResponseEnum
{
    case UNKOWN;
    case NOT_FOUND;
    case ERROR;
    case ALREADY_EXISTS;
    case SUCCESS;
}
